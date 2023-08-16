<?php

class ControllerExtensionPaymentFreedompay extends Controller
{
    public function index()
    {
        $this->language->load('extension/payment/freedompay');
        $this->load->model('checkout/order');
        $this->load->model('account/order');
        $this->load->model('extension/total/coupon');
        $this->load->model('extension/total/voucher');
        $this->load->model('extension/payment/freedompay');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);

        $strOrderDescription = '';

        foreach ($order_products as $product) {
            $strOrderDescription .= @$product['name'] . '*' . @$product['quantity'] . '; ';
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $merchant_id = $this->config->get('payment_freedompay_merchant_id');
        $secret_word = $this->config->get('payment_freedompay_secret_word');
        $lifetime = $this->config->get('payment_freedompay_lifetime');

        $this->load->model('extension/payment/freedompay');

        $strCurrency = $order_info['currency_code'];

        if ($strCurrency === 'RUR') {
            $strCurrency = 'RUB';
        }

        $baseUrl = explode('index.php', $this->request->server['HTTP_REFERER'])[0];

        $arrReq = array(
            'pg_amount'             => $order_info['total'],
            'pg_check_url'          => $baseUrl . 'index.php?route=extension/payment/freedompay/check',
            'pg_description'        => $this->model_extension_payment_freedompay->stringFieldFormatting(
                $strOrderDescription
            ),
            'pg_encoding'           => 'UTF-8',
            'pg_currency'           => $strCurrency,
            'pg_user_ip'            => $_SERVER['REMOTE_ADDR'],
            'pg_lifetime'           => !empty($lifetime) ? $lifetime * 3600 : 86400,
            'pg_merchant_id'        => $merchant_id,
            'pg_order_id'           => $order_info['order_id'],
            'pg_result_url'         => $baseUrl . 'index.php?route=extension/payment/freedompay/callback',
            'pg_request_method'     => 'GET',
            'pg_salt'               => rand(21, 43433),
            'pg_success_url'        => $baseUrl . 'index.php?route=checkout/success',
            'pg_failure_url'        => $baseUrl . 'index.php?route=checkout/failure',
            'pg_user_phone'         => (int)preg_replace('/\D/', '', $order_info['telephone']),
            'pg_user_contact_email' => $order_info['email']
        );

        if ($this->config->get('payment_freedompay_test') == 1) {
            $arrReq['pg_testing_mode'] = 1;
        }

        if (isset($this->session->data['coupon'])) {
            $coupon = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
        }

        if (isset($this->session->data['voucher'])) {
            $voucher = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);
        }

        if ($this->config->get('payment_freedompay_ofd') == 1) {
            foreach ($this->model_account_order->getOrderProducts($this->session->data['order_id']) as $value) {
                $count = count($this->model_account_order->getOrderProducts($this->session->data['order_id']));

                if (isset($coupon)) {
                    $price = $this->model_extension_payment_freedompay->getPositionsProductToOfd(
                        $value['price'],
                        'coupon',
                        $coupon,
                        $count
                    );
                } elseif (isset($voucher)) {
                    $price = $this->model_extension_payment_freedompay->getPositionsProductToOfd(
                        $value['price'],
                        'voucher',
                        $voucher,
                        $count
                    );
                } else {
                    $price = $value['price'];
                }

                $arrReq['pg_receipt_positions'][] = [
                    'count'    => $value['quantity'],
                    'name'     => $this->model_extension_payment_freedompay->stringFieldFormatting($value['name']),
                    'price'    => $price,
                    'tax_type' => $this->config->get('payment_freedompay_ofd_tax_type')
                ];
            }

            if (isset($this->session->data['shipping_method']) && $this->config->get(
                    'payment_freedompay_ofd_shipping'
                )) {
                if (isset($coupon) && $coupon['shipping'] == '1') {
                    $price = 0;
                } else {
                    $price = $this->session->data['shipping_method']['cost'];
                }

                $arrReq['pg_receipt_positions'][] = [
                    'count'    => 1,
                    'name'     => $this->model_extension_payment_freedompay->stringFieldFormatting(
                        $this->session->data['shipping_method']['title']
                    ),
                    'price'    => $price,
                    'tax_type' => $this->config->get('payment_freedompay_ofd_tax_type')
                ];
            }
        }

        $arrReq['pg_sig'] = $this->model_extension_payment_freedompay->make('payment.php', $arrReq, $secret_word);

        $data['arrReq'] = $arrReq;
        $data['action'] = 'https://' . $this->config->get('payment_freedompay_api_url') . '/payment.php';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/freedompay')) {
            return $this->load->view($this->config->get('config_template') . '/extension/payment/freedompay', $data);
        }

        return $this->load->view('extension/payment/freedompay', $data);
    }

    public function check()
    {
        $this->language->load('extension/payment/freedompay');
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/freedompay');

        $arrResponse = array();

        if (!empty($this->request->post)) {
            $data = $this->request->post;
        } else {
            $data = $this->request->get;
        }

        $pg_sig = !empty($data['pg_sig']) ? $data['pg_sig'] : '';
        unset($data['pg_sig']);

        $secret_word = $this->config->get('payment_freedompay_secret_word');

        // Получаем информацию о заказе
        $order_id = $data['pg_order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $arrResponse['pg_salt'] = $data['pg_salt'];

        if (isset($order_info['order_id'])) {
            $arrResponse['pg_status'] = 'ok';
            $arrResponse['pg_description'] = '';
        } else {
            $arrResponse['pg_status'] = 'rejected';
            $arrResponse['pg_description'] = $this->language->get('err_order_not_found');
        }

        $arrResponse['pg_sig'] = $this->model_extension_payment_freedompay->make(
            'index.php',
            $arrResponse,
            $secret_word
        );

        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        echo "<response>\r\n";
        echo "<pg_salt>" . $arrResponse['pg_salt'] . "</pg_salt>\r\n";
        echo "<pg_status>" . $arrResponse['pg_status'] . "</pg_status>\r\n";
        echo "<pg_description>" . htmlentities($arrResponse['pg_description']) . "</pg_description>\r\n";
        echo "<pg_sig>" . $arrResponse['pg_sig'] . "</pg_sig>\r\n";
        echo "</response>";
    }

    public function callback()
    {
        $this->language->load('extension/payment/freedompay');
        $this->load->model('extension/payment/freedompay');
        $this->load->model('checkout/order');

        $arrResponse = array();

        if (!empty($this->request->post)) {
            $data = $this->request->post;
        } else {
            $data = $this->request->get;
        }

        $pg_sig = $data['pg_sig'];
        unset($data['pg_sig']);

        $secret_word = $this->config->get('payment_freedompay_secret_word');

        $order_id = $data['pg_order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $arrResponse['pg_salt'] = $data['pg_salt'];

        if ($data['pg_result'] != 1) {
            $arrResponse['pg_status'] = 'failed';
            $arrResponse['pg_error_description'] = '';
        } elseif (isset($order_info['order_id'])) {
            $arrResponse['pg_status'] = 'ok';
            $arrResponse['pg_error_description'] = '';
        } else {
            $arrResponse['pg_status'] = 'rejected';
            $arrResponse['pg_error_description'] = $this->language->get('err_order_not_found');
        }

        $arrResponse['pg_sig'] = $this->model_extension_payment_freedompay->make(
            'index.php',
            $arrResponse,
            $secret_word
        );

        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        echo "<response>\r\n";
        echo "<pg_salt>" . $arrResponse['pg_salt'] . "</pg_salt>\r\n";
        echo "<pg_status>" . $arrResponse['pg_status'] . "</pg_status>\r\n";
        echo "<pg_error_description>" . htmlentities(
                $arrResponse['pg_error_description']
            ) . "</pg_error_description>\r\n";
        echo "<pg_sig>" . $arrResponse['pg_sig'] . "</pg_sig>\r\n";
        echo "</response>\r\n";

        if ($arrResponse['pg_status'] === 'ok') {
            if ($order_info['order_status_id'] == 0) {
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->config->get('payment_freedompay_order_status_id'),
                    'freedompay'
                );

                return;
            }

            if ($order_info['order_status_id'] != $this->config->get('payment_freedompay_order_status_id')) {
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->config->get('payment_freedompay_order_status_id'),
                    'freedompay',
                    true
                );
            }
        }
    }

    private function getVersion()
    {
        $version = explode('.', VERSION);

        return array(
            'alpha'  => $version[0],
            'beta'   => $version[1],
            'rc'     => $version[2],
            'public' => $version[3]
        );
    }
}
