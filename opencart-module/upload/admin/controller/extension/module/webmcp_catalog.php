<?php
class ControllerExtensionModuleWebmcpCatalog extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/webmcp_catalog');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_webmcp_catalog', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/webmcp_catalog', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_live_results'] = $this->language->get('entry_live_results');
        $data['entry_max_results'] = $this->language->get('entry_max_results');
        $data['help_live_results'] = $this->language->get('help_live_results');
        $data['help_max_results'] = $this->language->get('help_max_results');
        $data['text_about'] = $this->language->get('text_about');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        $data['breadcrumbs'][] = array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        $data['breadcrumbs'][] = array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/webmcp_catalog', 'user_token=' . $this->session->data['user_token'], true));

        $data['action'] = $this->url->link('extension/module/webmcp_catalog', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $fields = array('module_webmcp_catalog_status' => 0, 'module_webmcp_catalog_live_results' => 1, 'module_webmcp_catalog_max_results' => 6);
        foreach ($fields as $key => $default) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $value = $this->config->get($key);
                $data[$key] = ($value === null || $value === '') ? $default : $value;
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/webmcp_catalog', $data));
    }

    public function install() {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/webmcp_catalog');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/webmcp_catalog');
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_webmcp_catalog', array(
            'module_webmcp_catalog_status' => 0,
            'module_webmcp_catalog_live_results' => 1,
            'module_webmcp_catalog_max_results' => 6
        ));
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_webmcp_catalog');
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/webmcp_catalog')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (isset($this->request->post['module_webmcp_catalog_max_results'])) {
            $max = (int)$this->request->post['module_webmcp_catalog_max_results'];
            if ($max < 1 || $max > 10) {
                $this->error['warning'] = $this->language->get('error_max_results');
            }
        }
        return !$this->error;
    }
}
