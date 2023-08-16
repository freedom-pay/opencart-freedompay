<?php

class ControllerExtensionPaymentFreedompay extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/freedompay');

        $this->document->setTitle = $this->language->get('heading_title');

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_freedompay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect(
                $this->url->link(
                    'marketplace/extension',
                    'user_token=' . $this->session->data['user_token'] . '&type=payment',
                    true
                )
            );
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['entry_payment_name'] = $this->language->get('entry_payment_name');
        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_secret_word'] = $this->language->get('entry_secret_word');
        $data['entry_api_url'] = $this->language->get('entry_api_url');
        $data['tooltip_api_url'] = $this->language->get('tooltip_api_url');
        $data['entry_lifetime'] = $this->language->get('entry_lifetime');
        $data['entry_success_url'] = $this->language->get('entry_success_url');
        $data['entry_fail_url'] = $this->language->get('entry_fail_url');
        $data['entry_result_url'] = $this->language->get('entry_result_url');
        $data['entry_ofd'] = $this->language->get('entry_ofd');
        $data['copy_result_url'] = HTTP_CATALOG . 'index.php?route=extension/payment/freedompay/callback';
        $data['copy_success_url'] = HTTP_CATALOG . 'index.php?route=checkout/success';
        $data['copy_fail_url'] = HTTP_CATALOG . 'index.php?route=checkout/failure';
        $data['tooltip_payment_name'] = $this->language->get('tooltip_payment_name');
        $data['tooltip_merchant_id'] = $this->language->get('tooltip_merchant_id');
        $data['tooltip_secret_word'] = $this->language->get('tooltip_secret_word');
        $data['tooltip_result_url'] = $this->language->get('tooltip_result_url');
        $data['tooltip_success_url'] = $this->language->get('tooltip_success_url');
        $data['tooltip_fail_url'] = $this->language->get('tooltip_fail_url');
        $data['tooltip_test'] = $this->language->get('tooltip_test');
        $data['tooltip_lifetime'] = $this->language->get('tooltip_lifetime');
        $data['tooltip_order_status'] = $this->language->get('tooltip_order_status');
        $data['tooltip_status'] = $this->language->get('tooltip_status');
        $data['tooltip_sort_order'] = $this->language->get('tooltip_sort_order');
        $data['entry_test'] = $this->language->get('entry_test');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entity_ofd_tax_type'] = $this->language->get('entity_ofd_tax_type');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_general'] = $this->language->get('tab_general');
        $data['tooltip_ofd_tax_type'] = $this->language->get('tooltip_ofd_tax_type');
        $data['tooltip_ofd_shipping'] = $this->language->get('tooltip_ofd_shipping');
        $data['entity_ofd_shipping'] = $this->language->get('entity_ofd_shipping');

        $data['tax_type_vat_none'] = $this->language->get('tax_type_vat_none');
        $data['tax_type_vat_0'] = $this->language->get('tax_type_vat_0');
        $data['tax_type_vat_12'] = $this->language->get('tax_type_vat_12');
        $data['tax_type_vat_112'] = $this->language->get('tax_type_vat_112');
        $data['tax_type_vat_18'] = $this->language->get('tax_type_vat_18');
        $data['tax_type_vat_118'] = $this->language->get('tax_type_vat_118');
        $data['tax_type_vat_10'] = $this->language->get('tax_type_vat_10');
        $data['tax_type_vat_110'] = $this->language->get('tax_type_vat_110');
        $data['tax_type_vat_20'] = $this->language->get('tax_type_vat_20');
        $data['tax_type_vat_120'] = $this->language->get('tax_type_vat_120');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['payment_name'])) {
            $data['error_payment_name'] = $this->error['payment_name'];
        } else {
            $data['error_payment_name'] = '';
        }

        if (isset($this->error['merchant_id'])) {
            $data['error_merchant_id'] = $this->error['merchant_id'];
        } else {
            $data['error_merchant_id'] = '';
        }

        if (isset($this->error['secret_word'])) {
            $data['error_secret_word'] = $this->error['secret_word'];
        } else {
            $data['error_secret_word'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link(
                'extension/payment',
                'user_token=' . $this->session->data['user_token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link(
                'extension/payment/freedompay',
                'user_token=' . $this->session->data['user_token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link(
            'extension/payment/freedompay',
            'user_token=' . $this->session->data['user_token'],
            'SSL'
        );

        $data['cancel'] = $this->url->link(
            'extension/payment',
            'user_token=' . $this->session->data['user_token'],
            'SSL'
        );

        if (isset($this->request->post['payment_freedompay_payment_name'])) {
            $data['payment_freedompay_payment_name'] = $this->request->post['payment_freedompay_payment_name'];
        } else {
            $data['payment_freedompay_payment_name'] = $this->config->get('payment_freedompay_payment_name');
        }

        if (isset($this->request->post['payment_freedompay_merchant_id'])) {
            $data['payment_freedompay_merchant_id'] = $this->request->post['payment_freedompay_merchant_id'];
        } else {
            $data['payment_freedompay_merchant_id'] = $this->config->get('payment_freedompay_merchant_id');
        }

        if (isset($this->request->post['payment_freedompay_secret_word'])) {
            $data['payment_freedompay_secret_word'] = $this->request->post['payment_freedompay_secret_word'];
        } else {
            $data['payment_freedompay_secret_word'] = $this->config->get('payment_freedompay_secret_word');
        }

        $data['api_url_list'] = explode(',', 'api.freedompay.money,api.freedompay.uz');

        if (isset($this->request->post['payment_freedompay_api_url'])) {
            $data['payment_freedompay_api_url'] = $this->request->post['payment_freedompay_api_url'];
        } else {
            $data['payment_freedompay_api_url'] = $this->config->get('payment_freedompay_api_url');
        }

        if (isset($this->request->post['payment_freedompay_test'])) {
            $data['payment_freedompay_test'] = $this->request->post['payment_freedompay_test'];
        } else {
            $data['payment_freedompay_test'] = $this->config->get('payment_freedompay_test');
        }

        if (isset($this->request->post['freedompay_lifetime'])) {
            $data['payment_freedompay_lifetime'] = $this->request->post['payment_freedompay_lifetime'];
        } else {
            $data['payment_freedompay_lifetime'] = $this->config->get('payment_freedompay_lifetime');
        }

        if (isset($this->request->post['payment_freedompay_order_status_id'])) {
            $data['payment_freedompay_order_status_id'] = $this->request->post['payment_freedompay_order_status_id'];
        } else {
            $data['payment_freedompay_order_status_id'] = $this->config->get('payment_freedompay_order_status_id');
        }

        if (isset($this->request->post['payment_freedompay_ofd'])) {
            $data['payment_freedompay_ofd'] = $this->request->post['payment_freedompay_ofd'];
        } else {
            $data['payment_freedompay_ofd'] = $this->config->get('payment_freedompay_ofd');
        }

        if (isset($this->request->post['payment_freedompay_ofd_tax_type'])) {
            $data['payment_freedompay_ofd_tax_type'] = $this->request->post['payment_freedompay_ofd_tax_type'];
        } else {
            $data['payment_freedompay_ofd_tax_type'] = $this->config->get('payment_freedompay_ofd_tax_type');
        }

        if (isset($this->request->post['payment_freedompay_ofd_shipping'])) {
            $data['payment_freedompay_ofd_shipping'] = $this->request->post['payment_freedompay_ofd_shipping'];
        } else {
            $data['payment_freedompay_ofd_shipping'] = $this->config->get('payment_freedompay_ofd_shipping');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_freedompay_geo_zone_id'])) {
            $data['payment_freedompay_geo_zone_id'] = $this->request->post['payment_freedompay_geo_zone_id'];
        } else {
            $data['payment_freedompay_geo_zone_id'] = $this->config->get('payment_freedompay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_freedompay_status'])) {
            $data['payment_freedompay_status'] = $this->request->post['payment_freedompay_status'];
        } else {
            $data['payment_freedompay_status'] = $this->config->get('payment_freedompay_status');
        }

        if (isset($this->request->post['payment_freedompay_sort_order'])) {
            $data['payment_freedompay_sort_order'] = $this->request->post['payment_freedompay_sort_order'];
        } else {
            $data['payment_freedompay_sort_order'] = $this->config->get('payment_freedompay_sort_order');
        }

        $this->template = 'extension/payment/freedompay';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/payment/freedompay', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/freedompay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_freedompay_payment_name']) {
            $this->error['payment_name'] = $this->language->get('error_payment_name');
        }

        if (!$this->request->post['payment_freedompay_merchant_id']) {
            $this->error['merchant_id'] = $this->language->get('error_merchant_id');
        }

        if (!$this->request->post['payment_freedompay_secret_word']) {
            $this->error['secret_word'] = $this->language->get('error_secret_word');
        }

        if (!$this->error) {
            return true;
        }

        return false;
    }
}
