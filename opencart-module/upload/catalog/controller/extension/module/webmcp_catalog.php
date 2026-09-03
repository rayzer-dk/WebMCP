<?php
class ControllerExtensionModuleWebmcpCatalog extends Controller {
    public function search() {
        if (!$this->enabled()) {
            return $this->json(array('ok' => false, 'error' => 'module_disabled'), 404);
        }
        if (!$this->allowRequest()) {
            return;
        }
        $payload = $this->readJson(32768);
        if ($payload === null) {
            return;
        }
        $locale = isset($payload['locale']) ? (string)$payload['locale'] : $this->currentLocale();
        $this->load->model('extension/module/webmcp_catalog');
        $result = $this->model_extension_module_webmcp_catalog->searchProducts($payload, $locale);
        return $this->json($result, !empty($result['ok']) ? 200 : 400);
    }

    public function product() {
        if (!$this->enabled()) {
            return $this->json(array('ok' => false, 'error' => 'module_disabled'), 404);
        }
        if (!$this->allowRequest()) {
            return;
        }
        $payload = $this->readJson(16384);
        if ($payload === null) {
            return;
        }
        $product_id = isset($payload['product_id']) ? (int)$payload['product_id'] : 0;
        if ($product_id < 1) {
            return $this->json(array('ok' => false, 'error' => 'product_id_required'), 400);
        }
        $locale = isset($payload['locale']) ? (string)$payload['locale'] : $this->currentLocale();
        $this->load->model('extension/module/webmcp_catalog');
        $result = $this->model_extension_module_webmcp_catalog->getProduct($product_id, $locale);
        return $this->json($result, !empty($result['ok']) ? 200 : 404);
    }

    public function cart() {
        if (!$this->enabled()) {
            return $this->json(array('ok' => false, 'error' => 'module_disabled'), 404);
        }
        if ((int)$this->config->get('module_webmcp_catalog_cart_action') !== 1) {
            return $this->json(array('ok' => false, 'error' => 'cart_action_disabled'), 403);
        }
        if (!$this->allowRequest()) {
            return;
        }

        $payload = $this->readJson(8192);
        if ($payload === null) {
            return;
        }

        $product_id = isset($payload['product_id']) ? (int)$payload['product_id'] : 0;
        $quantity = isset($payload['quantity']) ? (int)$payload['quantity'] : 1;
        if ($product_id < 1) {
            return $this->json(array('ok' => false, 'error' => 'product_id_required'), 400);
        }
        if ($quantity < 1 || $quantity > 999) {
            return $this->json(array('ok' => false, 'error' => 'invalid_quantity'), 400);
        }

        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        if (!$product || empty($product['status'])) {
            return $this->json(array('ok' => false, 'error' => 'product_not_found'), 404);
        }

        $options = $this->model_catalog_product->getProductOptions($product_id);
        foreach ($options as $option) {
            if (!empty($option['required'])) {
                return $this->json(array(
                    'ok' => false,
                    'error' => 'product_options_required',
                    'product_id' => $product_id,
                    'message' => 'This product requires options and must be completed by the shopper.'
                ), 400);
            }
        }

        $this->cart->add($product_id, $quantity);
        $currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $count = (int)$this->cart->countProducts();
        $total_value = (float)$this->cart->getTotal();
        $total = $this->currency->format($total_value, $currency);

        return $this->json(array(
            'ok' => true,
            'action' => 'add_to_cart',
            'product_id' => $product_id,
            'quantity' => $quantity,
            'product_name' => isset($product['name']) ? (string)$product['name'] : '',
            'cart_item_count' => $count,
            'cart_total' => $total,
            'action_receipt' => array(
                'status' => 'committed',
                'scope' => 'cart_only',
                'order_placed' => false,
                'payment_performed' => false,
                'reversible_by_user' => true
            ),
            'human_agent_shared_state' => array(
                'cart_updated' => true,
                'visible_to_human' => true
            )
        ), 200);
    }

    private function enabled() {
        return (int)$this->config->get('module_webmcp_catalog_status') === 1;
    }

    private function allowRequest() {
        $method = isset($this->request->server['REQUEST_METHOD']) ? strtoupper((string)$this->request->server['REQUEST_METHOD']) : 'GET';
        if ($method !== 'POST') {
            $this->response->addHeader('Allow: POST');
            $this->json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
            return false;
        }

        $content_type = isset($this->request->server['CONTENT_TYPE']) ? strtolower((string)$this->request->server['CONTENT_TYPE']) : '';
        if (strpos($content_type, 'application/json') !== 0) {
            $this->json(array('ok' => false, 'error' => 'content_type_must_be_json'), 415);
            return false;
        }

        if (!$this->sameOrigin()) {
            $this->json(array('ok' => false, 'error' => 'origin_not_allowed'), 403);
            return false;
        }

        if (!$this->rateLimit()) {
            $this->response->addHeader('Retry-After: 60');
            $this->json(array('ok' => false, 'error' => 'rate_limited'), 429);
            return false;
        }

        return true;
    }

    private function sameOrigin() {
        $origin = isset($this->request->server['HTTP_ORIGIN']) ? trim((string)$this->request->server['HTTP_ORIGIN']) : '';
        $host = isset($this->request->server['HTTP_HOST']) ? strtolower(trim((string)$this->request->server['HTTP_HOST'])) : '';
        if ($origin !== '') {
            $origin_host = parse_url($origin, PHP_URL_HOST);
            if (!is_string($origin_host) || strtolower($origin_host) !== preg_replace('/:\\d+$/', '', $host)) {
                return false;
            }
        }
        $fetch_site = isset($this->request->server['HTTP_SEC_FETCH_SITE']) ? strtolower(trim((string)$this->request->server['HTTP_SEC_FETCH_SITE'])) : '';
        if ($fetch_site !== '' && !in_array($fetch_site, array('same-origin', 'same-site', 'none'), true)) {
            return false;
        }
        return true;
    }

    private function rateLimit() {
        $now = time();
        $key = 'webmcp_catalog_rate';
        $state = isset($this->session->data[$key]) && is_array($this->session->data[$key]) ? $this->session->data[$key] : array('start' => $now, 'count' => 0);
        if (!isset($state['start']) || $now - (int)$state['start'] >= 60) {
            $state = array('start' => $now, 'count' => 0);
        }
        $state['count'] = (int)$state['count'] + 1;
        $this->session->data[$key] = $state;
        return $state['count'] <= 120;
    }

    private function readJson($max_bytes) {
        $length = isset($this->request->server['CONTENT_LENGTH']) ? (int)$this->request->server['CONTENT_LENGTH'] : 0;
        if ($length > $max_bytes) {
            $this->json(array('ok' => false, 'error' => 'payload_too_large'), 413);
            return null;
        }
        $raw = (string)file_get_contents('php://input');
        if (strlen($raw) > $max_bytes) {
            $this->json(array('ok' => false, 'error' => 'payload_too_large'), 413);
            return null;
        }
        $payload = json_decode($raw, true);
        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            $this->json(array('ok' => false, 'error' => 'invalid_json'), 400);
            return null;
        }
        return $payload;
    }

    private function currentLocale() {
        $locale = isset($this->session->data['language']) ? strtolower(trim((string)$this->session->data['language'])) : '';
        if ($locale === '') {
            $locale = strtolower(trim((string)$this->config->get('config_language')));
        }
        return preg_replace('/[^a-z0-9_.-]/', '', $locale);
    }

    private function json($data, $status) {
        $status_text = array(
            200 => 'OK', 400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found',
            405 => 'Method Not Allowed', 413 => 'Payload Too Large', 415 => 'Unsupported Media Type',
            429 => 'Too Many Requests'
        );
        if ($status !== 200) {
            $this->response->addHeader('HTTP/1.1 ' . (int)$status . ' ' . (isset($status_text[$status]) ? $status_text[$status] : 'Error'));
        }
        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $this->response->addHeader('Cache-Control: no-store');
        $this->response->addHeader('X-Content-Type-Options: nosniff');
        $this->response->setOutput(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
