<?php

/*
	Class operate depended on Paypal REST API
	
	Last Update: 2015-06-24
		
	Reference
		https://developer.paypal.com/developer/applications
		https://developer.paypal.com/webapps/developer/docs/api/
		https://devtools-paypal.com/hateoas/index.html
*/

class PaypalRest
{
    public $error_msg = "";

    private $sandbox_mode = false;

    private $sandbox_endpoint = 'https://api.sandbox.paypal.com/';
    private $endpoint = 'https://api.paypal.com/';

    private $access_token = '';

    private $experience_profile_id = '';

    private $item_list = array();

    private $shipping_fee = "0.00";

    private $shipping_discount = "0.00";

    private $currencys = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY', 'USD');

    public function __construct($client_id, $secret, $sandbox = false)
    {
        if (empty($client_id) or empty($secret)) {
            die('Paypal require application register, please go to https://developer.paypal.com/developer/applications register your application.');
        }
        $this->sandbox_mode = $sandbox;
        $this->access_token = $this->get_access_token($client_id, $secret);
    }

    public function get_access_token($client_id, $secret)
    {
        $query = array(
            'grant_type' => 'client_credentials'
        );
        $header = array(
            'Accept: application/json',
            'Accept-Language: en_US',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url() . 'v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $secret);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == null) {
            die('Paypal: server api cannot be connect');
        }
        $json = json_decode($response, true);
        if ($json == null or !empty($json['error_description'])) {
            die('Paypal: ' . $json['error_description']);
        }
        return $json['access_token'];
    }

    public function get_api_url()
    {
        return ($this->sandbox_mode) ? $this->sandbox_endpoint : $this->endpoint;
    }

    public function create_experience_profile($name, $brand_name, $logo_image_url = '', $locale_code = '', $allow_note = false, $no_shipping = 1, $address_override = 0)
    {
        echo "\nThis function design for initialize only. DO NOT use at runtime. \n\n";
        if (empty($name) or empty($brand_name)) {
            die('Variable $name and $brand_name cannot be blank.');
        }
        // $logo_image_url size is 190 x 60 , Only support .gif, .jpg, or .png .
        if (!in_array($locale_code, array('AU', 'AT', 'BE', 'BR', 'CA', 'CH', 'CN', 'DE', 'ES', 'GB', 'FR', 'IT', 'NL', 'PL', 'PT', 'RU', 'US', 'da_DK', 'he_IL', 'id_ID', 'ja_JP', 'no_NO', 'pt_BR', 'ru_RU', 'sv_SE', 'th_TH', 'tr_TR', 'zh_CN', 'zh_HK', 'zh_TW'))) {
            die('Variable $locale_code should within values: AU, AT, BE, BR, CA, CH, CN, DE, ES, GB, FR, IT, NL, PL, PT, RU, US, da_DK, he_IL, id_ID, ja_JP, no_NO, pt_BR, ru_RU, sv_SE, th_TH, tr_TR, zh_CN, zh_HK or zh_TW.');
        }
        $data = array(
            'name' => $name,
            'input_fields' => array(
                'allow_note' => $allow_note,
                'no_shipping' => $no_shipping,
                'address_override' => $address_override,
            ),
        );
        $data['presentation']['brand_name'] = $brand_name;
        if (!empty($logo_image_url)) {
            $data['presentation']['logo_image'] = $logo_image_url;
        }
        if (!empty($locale_code)) {
            $data['presentation']['locale_code'] = $locale_code;
        }
        $post_data = json_encode($data);
        $header = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Length: ' . strlen($post_data),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url() . 'v1/payment-experience/web-profiles');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == null) {
            die('Paypal: server api cannot be connect');
        }
        $json = json_decode($response, true);
        if ($json == null or !empty($json['error_description'])) {
            die('Paypal: ' . $json['error_description']);
        }
        echo "Web experience profile ID: " . $json['id'] . "\n\n Please recognize your profile code for furture use.";
        exit;
    }

    /*
    Reference
        https://developer.paypal.com/docs/integration/direct/make-your-first-call/
    */

    public function create_payment($return_url, $cancel_url, $invoice_number = "", $description = "")
    {
        if (empty($this->item_list)) {
            return false;
        }
        if (($amount_object = $this->get_amount_object($this->item_list)) == false) {
            return false;
        }
        $transactions = array(
            'amount' => $amount_object,
            'item_list' => $this->item_list,
        );
        if (!empty($invoice_number)) {
            $transactions['invoice_number'] = $invoice_number;
        }
        if (!empty($description)) {
            $transactions['description'] = $description;
        }
        $data = array(
            'intent' => 'sale',
            'payer' => array(
                'payment_method' => 'paypal',
            ),
            'transactions' => array(
                $transactions
            ),
            'redirect_urls' => array(
                'return_url' => $return_url,
                'cancel_url' => $cancel_url,
            ),
        );
        if (!empty($this->experience_profile_id)) {
            $data['experience_profile_id'] = $this->experience_profile_id;
        }
        $post_data = json_encode($data);
        $header = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Length: ' . strlen($post_data),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url() . 'v1/payments/payment');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == null) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        $json = json_decode($response, true);
        if ($json == null or !empty($json['error_description'])) {
            $this->error_msg = "Paypal: " . $json['error_description'];
            return false;
        }
        $payment = array(
            'id' => $json['id'],
            'create_time' => date('Y-m-d H:i:s', strtotime($json['create_time'])),
            'state' => $json['state'],
            'intent' => $json['intent'],
        );
        foreach ($json['links'] as $link) {
            $payment['url_' . $link['rel']] = $link['href'];
        }
        return $payment;
    }

    /*
    Reference
        https://developer.paypal.com/webapps/developer/docs/integration/direct/rest-experience-overview/#create-the-web-experience-profile
    */


    private function get_amount_object($item_list)
    {
        $currency = '';
        $total = 0;
        $tax = 0;
        if (empty($item_list['items'])) {
            return false;
        }
        foreach ($item_list['items'] as $index => $item) {
            if (empty($currency)) {
                $currency = $item['currency'];
            } else if ($currency != $item['currency']) {
                return false;
            }
            $total += $this->to_float($item['price']) * $this->to_int($item['quantity']);
            if (isset($item['tax'])) {
                $tax += $this->to_float($item['tax']) * $this->to_int($item['quantity']);
            }
        }
        if ($total == 0) {
            return false;
        }
        $amount_object = array(
            'currency' => $currency,
            'total' => number_format($total, 2),
        );
        // Handle shipping
        if (($shipping_fee = $this->to_float($this->shipping_fee)) > 0) {
            $amount_object['details']['subtotal'] = number_format($total, 2);

            $amount_object['details']['shipping'] = $this->shipping_fee;
            $total += $shipping_fee;

            if (($shipping_discount = $this->to_float($this->shipping_discount)) > 0) {
                $amount_object['details']['shipping_discount'] = $this->shipping_discount;
                $total -= $this->to_float($this->shipping_fee);
            }

            $amount_object['details']['tax'] = number_format($tax, 2);
            $amount_object['total'] = number_format($total + $tax, 2);
        }
        return $amount_object;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#create-a-payment
        https://developer.paypal.com/docs/api/#transaction-object
    */

    private function to_float($string_number)
    {
        return (float)str_replace(',', '', $string_number);
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#look-up-a-payment-resource
    */

    private function to_int($string_number)
    {
        return (int)str_replace(',', '', $string_number);
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#execute-an-approved-paypal-payment
    */

    public function look_up_payment($payment_id)
    {
        $header = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Language: en_US',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url() . 'v1/payments/payment/' . $payment_id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == null) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        $json = json_decode($response, true);
        if (!empty($json['error_description'])) {
            $this->error_msg = "Paypal: " . $json['error_description'];
            return false;
        }
        if (empty($json['id']) and !empty($json['message'])) {
            $this->error_msg = "Paypal: " . $json['name'] . ' ' . $json['message'];
            return false;
        }
        return $this->repack_payment($json);
    }

    private function repack_payment($json)
    {
        if (!isset($json['id'])) {
            return false;
        }
        $payment = array(
            'id' => $json['id'],
            'payer_id' => $json['payer']['payer_info']['payer_id'],
            'create_time' => date('Y-m-d H:i:s', strtotime($json['create_time'])),
            'update_time' => date('Y-m-d H:i:s', strtotime($json['update_time'])),
            'state' => $json['state'],
            'intent' => $json['intent'],
            'total_price' => $json['transactions'][0]['amount']['total'],
            'currency' => $json['transactions'][0]['amount']['currency'],
            'items' => $json['transactions'][0]['item_list']['items'],
        );
        if (!empty($json['transactions'][0]['related_resources'])) {
            $related_resources = $json['transactions'][0]['related_resources'];
            $payment['related_resources'] = array(
                'transaction_id' => $related_resources[0]['sale']['id'],
                'state' => $related_resources[0]['sale']['state'],
                'total_price' => $related_resources[0]['sale']['amount']['total'],
                'currency' => $related_resources[0]['sale']['amount']['currency'],
                'payment_mode' => $related_resources[0]['sale']['payment_mode'],
            );
        }
        return $payment;
    }

    /*
    Reference
        https://developer.paypal.com/webapps/developer/docs/api/#create-a-web-experience-profile
        https://developer.paypal.com/webapps/developer/docs/integration/direct/rest-experience-overview/#create-the-web-experience-profile
    */

    public function execute_payment($payment_id, $payer_id)
    {
        $data = array(
            'payer_id' => $payer_id,
        );
        $post_data = json_encode($data);
        $header = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Length: ' . strlen($post_data),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url() . 'v1/payments/payment/' . $payment_id . '/execute');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == null) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        $json = json_decode($response, true);
        if (!empty($json['error_description'])) {
            $this->error_msg = "Paypal: " . $json['error_description'];
            return false;
        }
        if (empty($json['id']) and !empty($json['message'])) {
            $this->error_msg = "Paypal: " . $json['name'] . ' ' . $json['message'];
            return false;
        }
        return $this->repack_payment($json);
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#itemlist-object
    */

    public function set_experience_profile_id($id)
    {
        $this->experience_profile_id = $id;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#itemlist-object
    */

    public function add_item($items)
    {
        if (!is_array($items) or empty($items['name']) or empty($items['quantity']) or empty($items['price']) or empty($items['currency'])) {
            return false;
        }
        $this->item_list['items'][] = $items;
        return true;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#details-object
    */

    public function set_shipping_address($addr)
    {
        if (!is_array($addr) or empty($addr['recipient_name']) or empty($addr['line1']) or empty($addr['city']) or empty($addr['country_code']) or empty($addr['postal_code'])) {
            return false;
        }
        $this->item_list['shipping_address'] = $addr;
        return true;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#details-object
    */

    public function add_shipping_fee($price)
    {
        if ($this->to_float($price) <= 0) {
            return false;
        }
        $this->shipping_fee = $price;
        return true;
    }



    /**********************************************
     *
     * Objects
     **********************************************/


    /*
    Reference
        https://developer.paypal.com/docs/api/#amount-object
        https://developer.paypal.com/docs/api/#details-object
    */
    public function add_shipping_discount($price)
    {
        if ($this->to_float($price) <= 0) {
            return false;
        }
        $this->shipping_discount = $price;
        return true;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#item-object
    */

    public function item_object($name, $quantity, $single_price, $currency, $sku = '', $tax = '', $description = '')
    {
        if (!in_array($currency, $this->currencys) or empty($name) or empty($quantity) or empty($single_price) or empty($currency)) {
            return false;
        }
        $item = array(
            'name' => $name,
            'quantity' => $quantity,
            'price' => $single_price,
            'currency' => $currency,
        );
        if (!empty($sku)) {
            $item['sku'] = $sku;
        }
        if (!empty($description)) {
            $item['description'] = $description;
        }
        if (!empty($tax)) {
            $item['tax'] = $tax;
        }
        return $item;
    }

    /*
    Reference
        https://developer.paypal.com/docs/api/#shippingaddress-object
    */
    public function address_object($recipient_name, $address_line_1, $address_line_2, $city, $country_code, $postal_code, $phone = '')
    {
        if (empty($recipient_name) or empty($address_line_1) or empty($address_line_2) or empty($city) or empty($country_code) or empty($postal_code)) {
            return false;
        }
        $addr = array(
            'recipient_name' => $recipient_name,
            'city' => $city,
            'country_code' => $country_code,
            'postal_code' => $postal_code,
        );
        if (!empty($phone)) {
            $addr['phone'] = $phone;
        }
        $addr['line1'] = $address_line_1;
        $addr['line2'] = $address_line_2;
        return $addr;
    }

}