<?php
class ModelExtensionModuleWebmcpCatalog extends Model {
    public function searchProducts($input, $requested_locale) {
        $query = isset($input['query']) ? trim((string)$input['query']) : '';
        if ($query === '') {
            return array('ok' => false, 'error' => 'query_required');
        }

        $locale = $this->resolveLocale($requested_locale);
        if (!$locale) {
            return array('ok' => false, 'error' => 'locale_not_available');
        }

        $constraints = $this->constraints($input, $query);
        $max_setting = (int)$this->config->get('module_webmcp_catalog_max_results');
        if ($max_setting < 1 || $max_setting > 10) $max_setting = 6;
        $limit = max(1, min($max_setting, $constraints['limit']));

        $terms = $this->queryTerms($query);
        if (!$terms) {
            return array('ok' => false, 'error' => 'query_has_no_searchable_terms');
        }
        $intent = $this->intentTerms($terms);
        $candidates = $this->candidateRows($query, $terms, $locale['language_id']);

        $results = array();
        foreach ($candidates as $row) {
            if ($intent && !$this->matchesIntent($row, $intent)) {
                continue;
            }
            $item = $this->hydrateProduct((int)$row['product_id'], $locale, $row);
            if (!$item) continue;
            if ($constraints['min_price'] !== null && $item['final_price'] < $constraints['min_price']) continue;
            if ($constraints['max_price'] !== null && $item['final_price'] > $constraints['max_price']) continue;
            if ($constraints['in_stock'] === true && !$item['in_stock']) continue;
            if ($constraints['in_stock'] === false && $item['in_stock']) continue;
            $item['_score'] = (int)$row['relevance_score'];
            $results[] = $item;
        }

        usort($results, function($a, $b) use ($constraints) {
            if ($constraints['in_stock'] === null && $a['in_stock'] !== $b['in_stock']) {
                return $a['in_stock'] ? -1 : 1;
            }
            if ($a['_score'] !== $b['_score']) return ($a['_score'] > $b['_score']) ? -1 : 1;
            if ($a['final_price'] != $b['final_price']) return ($a['final_price'] < $b['final_price']) ? -1 : 1;
            return strcmp($a['name'], $b['name']);
        });

        $has_more = count($results) > $limit;
        $results = array_slice($results, 0, $limit);
        foreach ($results as &$result) unset($result['_score']);
        unset($result);

        return array(
            'ok' => true,
            'authoritative' => true,
            'engine' => 'opencart_live',
            'query' => $query,
            'locale' => $locale['code'],
            'currency' => $this->currencyCode(),
            'live_price' => true,
            'live_stock' => true,
            'all_constraints_applied' => true,
            'complete_for_request' => !$has_more,
            'returned' => count($results),
            'requested_limit' => $limit,
            'has_more' => $has_more,
            'results' => $results
        );
    }

    public function getProduct($product_id, $requested_locale) {
        $locale = $this->resolveLocale($requested_locale);
        if (!$locale) return array('ok' => false, 'error' => 'locale_not_available');
        $row = $this->localizedProductRow($product_id, $locale['language_id']);
        if (!$row) return array('ok' => false, 'error' => 'product_not_found');
        $item = $this->hydrateProduct($product_id, $locale, $row, true);
        if (!$item) return array('ok' => false, 'error' => 'product_not_found');
        return array(
            'ok' => true,
            'authoritative' => true,
            'locale' => $locale['code'],
            'currency' => $this->currencyCode(),
            'live_price' => true,
            'live_stock' => true,
            'product' => $item
        );
    }

    private function candidateRows($query, $terms, $language_id) {
        $store_id = (int)$this->config->get('config_store_id');
        $language_id = (int)$language_id;
        $phrase = $this->db->escape($this->normalizePhrase($query));
        $where = array();
        $score = array();
        if ($phrase !== '') {
            $where[] = "pd.name LIKE '%" . $phrase . "%'";
            $where[] = "pd.description LIKE '%" . $phrase . "%'";
            $score[] = "CASE WHEN pd.name LIKE '%" . $phrase . "%' THEN 120 ELSE 0 END";
            $score[] = "MAX(CASE WHEN cd.name LIKE '%" . $phrase . "%' THEN 70 ELSE 0 END)";
        }
        foreach (array_slice($terms, 0, 10) as $term) {
            $term = $this->db->escape($term);
            $where[] = "pd.name LIKE '%" . $term . "%'";
            $where[] = "pd.description LIKE '%" . $term . "%'";
            $where[] = "p.model LIKE '%" . $term . "%'";
            $where[] = "p.sku LIKE '%" . $term . "%'";
            $where[] = "cd.name LIKE '%" . $term . "%'";
            $score[] = "CASE WHEN pd.name LIKE '%" . $term . "%' THEN 30 ELSE 0 END";
            $score[] = "MAX(CASE WHEN cd.name LIKE '%" . $term . "%' THEN 18 ELSE 0 END)";
            $score[] = "CASE WHEN p.model LIKE '%" . $term . "%' OR p.sku LIKE '%" . $term . "%' THEN 24 ELSE 0 END";
            $score[] = "CASE WHEN pd.description LIKE '%" . $term . "%' THEN 3 ELSE 0 END";
        }
        if (!$where) return array();
        $score_sql = $score ? implode(' + ', $score) : '0';
        $sql = "SELECT p.product_id, pd.name, pd.description, p.model, p.sku, "
             . "GROUP_CONCAT(DISTINCT cd.name ORDER BY c.sort_order, cd.name SEPARATOR ' > ') AS categories, "
             . "(" . $score_sql . ") AS relevance_score "
             . "FROM `" . DB_PREFIX . "product` p "
             . "INNER JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id AND pd.language_id = " . $language_id . ") "
             . "INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id = p.product_id AND p2s.store_id = " . $store_id . ") "
             . "LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p2c.product_id = p.product_id) "
             . "LEFT JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = 1) "
             . "LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (cd.category_id = c.category_id AND cd.language_id = " . $language_id . ") "
             . "WHERE p.status = 1 AND p.date_available <= NOW() AND (" . implode(' OR ', $where) . ") "
             . "GROUP BY p.product_id, pd.name, pd.description, p.model, p.sku "
             . "HAVING relevance_score > 0 ORDER BY relevance_score DESC, p.sort_order ASC, pd.name ASC LIMIT 80";
        return $this->db->query($sql)->rows;
    }

    private function localizedProductRow($product_id, $language_id) {
        $product_id = (int)$product_id;
        $language_id = (int)$language_id;
        $store_id = (int)$this->config->get('config_store_id');
        $sql = "SELECT p.product_id, pd.name, pd.description, p.model, p.sku, "
             . "GROUP_CONCAT(DISTINCT cd.name ORDER BY c.sort_order, cd.name SEPARATOR ' > ') AS categories, 999 AS relevance_score "
             . "FROM `" . DB_PREFIX . "product` p "
             . "INNER JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id=p.product_id AND pd.language_id=" . $language_id . ") "
             . "INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id=p.product_id AND p2s.store_id=" . $store_id . ") "
             . "LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p2c.product_id=p.product_id) "
             . "LEFT JOIN `" . DB_PREFIX . "category` c ON (c.category_id=p2c.category_id AND c.status=1) "
             . "LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (cd.category_id=c.category_id AND cd.language_id=" . $language_id . ") "
             . "WHERE p.product_id=" . $product_id . " AND p.status=1 AND p.date_available<=NOW() "
             . "GROUP BY p.product_id, pd.name, pd.description, p.model, p.sku LIMIT 1";
        $query = $this->db->query($sql);
        return $query->num_rows ? $query->row : null;
    }

    private function hydrateProduct($product_id, $locale, $row, $full = false) {
        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct((int)$product_id);
        if (!$product) return null;

        $currency = $this->currencyCode();
        $base_currency = (string)$this->config->get('config_currency');
        $raw_price = (!empty($product['special']) && (float)$product['special'] > 0) ? (float)$product['special'] : (float)$product['price'];
        $taxed = $this->tax->calculate($raw_price, (int)$product['tax_class_id'], (bool)$this->config->get('config_tax'));
        $final_price = (float)$this->currency->convert($taxed, $base_currency, $currency);

        $image_url = '';
        if (!empty($product['image'])) {
            $this->load->model('tool/image');
            $image_url = $this->model_tool_image->resize($product['image'], 320, 320);
        }

        $description = html_entity_decode(strip_tags((string)$row['description']), ENT_QUOTES, 'UTF-8');
        $description = preg_replace('/\s+/u', ' ', trim($description));
        if (!$full) $description = $this->shorten($description, 280);

        $result = array(
            'type' => 'product',
            'product_id' => (int)$product_id,
            'id' => (int)$product_id,
            'name' => (string)$row['name'],
            'model' => (string)$product['model'],
            'sku' => isset($product['sku']) ? (string)$product['sku'] : '',
            'description' => $description,
            'category_path' => isset($row['categories']) ? (string)$row['categories'] : '',
            'price' => round($final_price, 2),
            'final_price' => round($final_price, 2),
            'price_formatted' => $this->currency->format($final_price, $currency),
            'currency' => $currency,
            'quantity' => (int)$product['quantity'],
            'in_stock' => (int)$product['quantity'] > 0,
            'minimum' => max(1, (int)$product['minimum']),
            'manufacturer' => isset($product['manufacturer']) ? (string)$product['manufacturer'] : '',
            'url' => $this->url->link('product/product', 'product_id=' . (int)$product_id, true),
            'image_url' => $image_url,
            'thumbnail_url' => $image_url,
            'image_alt' => (string)$row['name'],
            'key_attributes' => $this->attributes((int)$product_id, (int)$locale['language_id'], $full ? 12 : 6)
        );
        if ($full) {
            $result['weight'] = isset($product['weight']) ? (float)$product['weight'] : null;
            $result['length'] = isset($product['length']) ? (float)$product['length'] : null;
            $result['width'] = isset($product['width']) ? (float)$product['width'] : null;
            $result['height'] = isset($product['height']) ? (float)$product['height'] : null;
        }
        return $result;
    }

    private function attributes($product_id, $language_id, $limit) {
        $sql = "SELECT ad.name, pa.text FROM `" . DB_PREFIX . "product_attribute` pa "
             . "INNER JOIN `" . DB_PREFIX . "attribute_description` ad ON (ad.attribute_id=pa.attribute_id AND ad.language_id=" . (int)$language_id . ") "
             . "WHERE pa.product_id=" . (int)$product_id . " AND pa.language_id=" . (int)$language_id . " "
             . "ORDER BY ad.name ASC LIMIT " . max(1, min(20, (int)$limit));
        $rows = $this->db->query($sql)->rows;
        $out = array();
        foreach ($rows as $row) {
            $text = preg_replace('/\s+/u', ' ', trim(strip_tags((string)$row['text'])));
            if ($text !== '') $out[] = array('name' => (string)$row['name'], 'text' => $this->shorten($text, 140));
        }
        return $out;
    }

    private function constraints($input, $query) {
        $limit = isset($input['limit']) ? (int)$input['limit'] : 0;
        if ($limit < 1) {
            if (preg_match('/(?:find|show|give|list|найди|покажи|дай|знайди|покажи)\s+([1-9]|10)\b/ui', $query, $m)) $limit = (int)$m[1];
        }
        if ($limit < 1) $limit = 3;

        $min_price = isset($input['min_price']) && is_numeric($input['min_price']) ? (float)$input['min_price'] : null;
        $max_price = isset($input['max_price']) && is_numeric($input['max_price']) ? (float)$input['max_price'] : null;
        if ($min_price === null || $max_price === null) {
            if (preg_match('/(?:from\s*)?([0-9][0-9\s,.]{2,})\s*(?:-|–|—|to|до)\s*([0-9][0-9\s,.]{2,})/ui', $query, $m)) {
                $a = $this->number($m[1]);
                $b = $this->number($m[2]);
                if ($a !== null && $b !== null) {
                    if ($min_price === null) $min_price = min($a, $b);
                    if ($max_price === null) $max_price = max($a, $b);
                }
            }
        }

        $in_stock = null;
        if (array_key_exists('in_stock', $input)) $in_stock = (bool)$input['in_stock'];
        if ($in_stock === null && preg_match('/\b(in stock|available now|в наличии|у наявності|в наявності)\b/ui', $query)) $in_stock = true;

        return array('limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price, 'in_stock' => $in_stock);
    }

    private function number($value) {
        $value = preg_replace('/[^0-9.,]/', '', (string)$value);
        if ($value === '') return null;
        if (substr_count($value, ',') === 1 && substr_count($value, '.') === 0) {
            $parts = explode(',', $value);
            if (strlen($parts[1]) === 3) $value = implode('', $parts); else $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
        return is_numeric($value) ? (float)$value : null;
    }

    private function queryTerms($query) {
        $text = $this->normalizePhrase($query);
        $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $stop = array('the','and','or','for','with','from','to','of','a','an','show','find','give','list','priced','price','uah','грн','та','і','або','для','від','до','з','найди','покажи','знайди','покажи');
        $terms = array();
        foreach ($parts as $part) {
            if (in_array($part, $stop, true)) continue;
            if (preg_match('/^[0-9]+(?:[.,][0-9]+)?$/', $part)) continue;
            if ($this->ulen($part) < 3) continue;
            $terms[$part] = true;
        }
        $expanded = array_keys($terms);
        $repeller = array('repeller','repellers','repellent','repellents','deterrent','deterrents','scarer','scarers','відлякувач','відлякувачі','відлякування','отпугиватель','отпугиватели','отпугивания');
        foreach ($expanded as $term) {
            if (in_array($term, $repeller, true)) {
                foreach ($repeller as $synonym) $terms[$synonym] = true;
                break;
            }
        }
        return array_slice(array_keys($terms), 0, 18);
    }

    private function intentTerms($terms) {
        $repeller = array('repeller','repellers','repellent','repellents','deterrent','deterrents','scarer','scarers','відлякувач','відлякувачі','відлякування','отпугиватель','отпугиватели','отпугивания');
        foreach ($terms as $term) {
            if (in_array($term, $repeller, true)) return $repeller;
        }
        return array();
    }

    private function matchesIntent($row, $intent) {
        $haystack = $this->normalizePhrase((string)$row['name'] . ' ' . (string)$row['categories'] . ' ' . (string)$row['model'] . ' ' . (string)$row['sku']);
        foreach ($intent as $term) {
            if (strpos($haystack, $term) !== false) return true;
        }
        return false;
    }

    private function normalizePhrase($text) {
        $text = html_entity_decode(strip_tags((string)$text), ENT_QUOTES, 'UTF-8');
        $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}._-]+/u', ' ', $text);
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function resolveLocale($requested) {
        $requested = strtolower(preg_replace('/[^a-z0-9_.-]/i', '', trim((string)$requested)));
        $query = $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE status=1 ORDER BY sort_order, language_id");
        if (!$query->num_rows) return null;
        foreach ($query->rows as $row) {
            if (strtolower((string)$row['code']) === $requested) return array('language_id' => (int)$row['language_id'], 'code' => strtolower((string)$row['code']));
        }
        $short = preg_replace('/[^a-z].*$/', '', $requested);
        $matches = array();
        foreach ($query->rows as $row) {
            $code = strtolower((string)$row['code']);
            if ($short !== '' && preg_replace('/[^a-z].*$/', '', $code) === $short) $matches[] = array('language_id' => (int)$row['language_id'], 'code' => $code);
        }
        if (count($matches) === 1) return $matches[0];
        $current = isset($this->session->data['language']) ? strtolower((string)$this->session->data['language']) : strtolower((string)$this->config->get('config_language'));
        foreach ($query->rows as $row) {
            if (strtolower((string)$row['code']) === $current) return array('language_id' => (int)$row['language_id'], 'code' => strtolower((string)$row['code']));
        }
        return array('language_id' => (int)$query->row['language_id'], 'code' => strtolower((string)$query->row['code']));
    }

    private function currencyCode() {
        $currency = isset($this->session->data['currency']) ? strtoupper(trim((string)$this->session->data['currency'])) : '';
        if ($currency === '') $currency = strtoupper(trim((string)$this->config->get('config_currency')));
        return preg_match('/^[A-Z]{3}$/', $currency) ? $currency : strtoupper((string)$this->config->get('config_currency'));
    }

    private function shorten($text, $length) {
        if ($this->ulen($text) <= $length) return $text;
        if (function_exists('mb_substr')) return rtrim(mb_substr($text, 0, $length - 1, 'UTF-8')) . '…';
        return rtrim(substr($text, 0, $length - 3)) . '...';
    }

    private function ulen($text) {
        return function_exists('mb_strlen') ? mb_strlen((string)$text, 'UTF-8') : strlen((string)$text);
    }
}
