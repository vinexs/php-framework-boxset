<?php

/*
	Class operate depended on Paypal Classic API
	
	Last Update: 2015-08-03
		
	Reference
		https://developer.paypal.com/docs/classic/
		https://www.paypal-apps.com/user/my-account/applications
		https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-and-managing-classic-api-credentials
		https://developer.paypal.com/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
		https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
*/

class PaypalClassic
{
    public $error_msg = "";

    private $sandbox_mode = false;

    private $sandbox_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    private $endpoint = 'https://api-3t.paypal.com/nvp';

    private $sandbox_exe_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
    private $exe_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';

    private $api_user = "";

    private $api_passwd = "";

    private $signature = "";

    private $api_version = "93.0";

    private $locale_code = "en_GB";

    private $item_list = array();

    private $require_shipping = false;

    private $shipping_address = array();

    private $shipping_fee = "0.00";

    private $shipping_discount = "0.00";

    private $currencys = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY', 'USD');

    public function __construct($username, $password, $signature, $sandbox = false)
    {
        if (empty($username) or empty($password) or empty($signature)) {
            die('Paypal require application register, please go to https://developer.paypal.com/docs/classic/.');
        }
        $this->sandbox_mode = $sandbox;
        $this->api_user = $username;
        $this->api_passwd = $password;
        $this->signature = $signature;
    }

    public function create_payment($return_url, $cancel_url, $invoice_number = "", $description = "")
    {
        if (empty($this->item_list)) {
            return false;
        }
        if (($amount_object = $this->get_amount_object($this->item_list)) == false) {
            return false;
        }
        $param = array(
            "USER" => $this->api_user,
            "PWD" => $this->api_passwd,
            "SIGNATURE" => $this->signature,
            "VERSION" => $this->api_version,
            "RETURNURL" => $return_url,
            "CANCELURL" => $cancel_url,
            "LOCALECODE" => $this->locale_code,
            "METHOD" => "SetExpressCheckout",
            "REQCONFIRMSHIPPING" => 0,
            "ALLOWNOTE" => 0,
            "ADDROVERRIDE" => 1,
            "SOLUTIONTYPE" => "Sole",
            "PAYMENTREQUEST_0_PAYMENTACTION" => "Sale",
        );
        if (!empty($description)) {
            $param['PAYMENTREQUEST_0_DESC'] = $description;
        }
        if (!empty($description)) {
            $param['PAYMENTREQUEST_0_INVNUM'] = $invoice_number;
        }
        if (!empty($this->style)) {
            $param = array_merge($param, $this->style);
        }
        $param = array_merge($param, $amount_object);
        if (!$this->require_shipping or empty($this->shipping_address)) {
            $param["NOSHIPPING"] = "1";
        } else {
            $param = array_merge($param, $this->shipping_address);
        }
        foreach ($this->item_list as $index => $item) {
            $param = array_merge($param, $item);
        }
        $param_string = http_build_query($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_string);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == false) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        parse_str($response, $json);
        if (empty($json['ACK']) or $json['ACK'] != 'Success') {
            $this->error_msg = "Paypal: " . $json['L_LONGMESSAGE0'];
            return false;
        }
        $token = $json['TOKEN'];
        $payment = array(
            'id' => $json['TOKEN'],
            'correalation_id' => $json['CORRELATIONID'],
            'create_time' => date('Y-m-d H:i:s'),
            'url_approval' => $this->get_exe_url() . $json['TOKEN'],
        );
        return $payment;
    }


    private function get_amount_object($item_list)
    {
        $item_amt = 0.0;
        $tax_amt = 0.0;
        $shipping_price = 0.0;
        $currency = '';
        foreach ($item_list as $index => $item) {
            $item_amt += $item_list[$index]['L_PAYMENTREQUEST_0_AMT' . $index] * $item_list[$index]['L_PAYMENTREQUEST_0_QTY' . $index];
            $item_tax = isset($item_list[$index]['L_PAYMENTREQUEST_0_TAXAMT' . $index]) ? $this->to_float($item_list[$index]['PAYMENTREQUEST_n_TAXAMT' . $index]) * $item_list[$index]['L_PAYMENTREQUEST_0_QTY' . $index] : 0;
            if ($item_tax > 0) {
                $tax_amt += $item_tax;
            }
            if (!empty($currency)) {
                if ($currency != $item_list[$index]['L_PAYMENTREQUEST_0_CURRENCYCODE' . $index]) {
                    $this->error_msg = "Item cannot have different currency.";
                    return false;
                }
            } else {
                $currency = $item_list[$index]['L_PAYMENTREQUEST_0_CURRENCYCODE' . $index];
            }
        }
        if ($this->shipping_fee > 0 or $this->shipping_discount > 0) {
            $shipping_price = max($this->shipping_fee - $this->shipping_discount, 0);
        }
        $amount = array(
            'PAYMENTREQUEST_0_AMT' => $item_amt + $tax_amt + $shipping_price,
            'PAYMENTREQUEST_0_ITEMAMT' => $item_amt,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $currency,
        );
        if ($shipping_price > 0) {
            $amount['PAYMENTREQUEST_0_SHIPPINGAMT'] = $shipping_price;
        }
        if ($tax_amt > 0) {
            $amount['PAYMENTREQUEST_0_TAXAMT'] = $tax_amt;
        }
        return $amount;
    }

    private function to_float($string_number)
    {
        return (float)str_replace(',', '', $string_number);
    }

    public function get_api_url()
    {
        return ($this->sandbox_mode) ? $this->sandbox_endpoint : $this->endpoint;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
    */

    public function get_exe_url()
    {
        return ($this->sandbox_mode) ? $this->sandbox_exe_url : $this->exe_url;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
    */

    public function look_up_payment($token)
    {
        $param = array(
            "USER" => $this->api_user,
            "PWD" => $this->api_passwd,
            "SIGNATURE" => $this->signature,
            "VERSION" => $this->api_version,
            "METHOD" => "GetExpressCheckoutDetails",
            "TOKEN" => $token,
        );
        $param_string = http_build_query($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_string);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == false) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        parse_str($response, $json);
        if (empty($json['ACK']) or $json['ACK'] != 'Success') {
            $this->error_msg = "Paypal: " . $json['L_LONGMESSAGE0'];
            return false;
        }
        $payment = array(
            'id' => $json['TOKEN'],
            'correalation_id' => $json['CORRELATIONID'],
            'create_time' => date('Y-m-d H:i:s', strtotime($json['TIMESTAMP'])),
            'amount' => $json['AMT'],
            'currency' => $json['CURRENCYCODE'],
        );
        if (!empty($json['PAYERID'])) {
            $payment['payer_id'] = $json['PAYERID'];
            $payment['payer_status'] = $json['PAYERSTATUS'];
        }
        return $payment;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/
    */
    public function execute_payment($payment)
    {
        if (empty($payment['id']) or empty($payment['correalation_id']) or empty($payment['payer_id']) or empty($payment['payer_status'])) {
            $this->error_msg = "Paypal: Invalid payment.";
            return false;
        }
        if ($payment['payer_status'] != 'verified') {
            $this->error_msg = "Paypal: Payer not verified.";
            return false;
        }
        $param = array(
            "USER" => $this->api_user,
            "PWD" => $this->api_passwd,
            "SIGNATURE" => $this->signature,
            "VERSION" => $this->api_version,
            "METHOD" => "DoExpressCheckoutPayment",
            "PAYMENTREQUEST_0_PAYMENTACTION" => 'SALE',
            "TOKEN" => $payment['id'],
            "PAYERID" => $payment['payer_id'],
            "PAYMENTREQUEST_0_AMT" => $payment['amount'],
            "PAYMENTREQUEST_0_CURRENCYCODE" => $payment['currency'],
        );
        $param_string = http_build_query($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_string);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == false) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        parse_str($response, $json);
        if (empty($json['ACK']) or $json['ACK'] != 'Success') {
            $this->error_msg = "Paypal: " . $json['L_LONGMESSAGE0'];
            return false;
        }
        if (($json['PAYMENTINFO_0_PAYMENTSTATUS'] != 'Completed' or empty($json['PAYMENTINFO_0_TRANSACTIONID']))) {
            $this->error_msg = "Paypal: Payment cannot be completed.";
            return false;
        }
        return $json['PAYMENTINFO_0_TRANSACTIONID'];
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/RefundTransaction_API_Operation_NVP/
    */
    public function refund_payment($transaction_id)
    {
        if (empty($transaction_id)) {
            $this->error_msg = "Paypal: Invalid payment.";
            return false;
        }
        $param = array(
            "USER" => $this->api_user,
            "PWD" => $this->api_passwd,
            "SIGNATURE" => $this->signature,
            "VERSION" => $this->api_version,
            "METHOD" => "RefundTransaction",
            "TRANSACTIONID" => $transaction_id,
            "REFUNDTYPE" => "Full",
        );
        $param_string = http_build_query($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_api_url());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_string);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == false) {
            $this->error_msg = "Paypal: server api cannot be connect\n\n" . $response;
            return false;
        }
        parse_str($response, $json);
        if (empty($json['ACK']) or $json['ACK'] != 'Success') {
            $this->error_msg = "Paypal: " . $json['L_LONGMESSAGE0'];
            return false;
        }
        return array(
            'transaction_id' => $json['REFUNDTRANSACTIONID'],
            'request_amount' => $json['TOTALREFUNDEDAMOUNT'],
            'paypal_handle_amount' => $json['FEEREFUNDAMT'],
            'returned_amount' => $json['NETREFUNDAMT'],
            'currency' => $json['CURRENCYCODE'],
        );
    }

    public function add_item($item)
    {
        if (!is_array($item)) {
            return false;
        }
        array_push($this->item_list, $item);
    }

    public function set_experience_profile($brand_name = '', $logo_img = '', $customer_service_no = '', $theme_color = '', $landing = 'Login')
    {
        $this->style = array();
        if (!empty($brand_name)) {
            $this->style['BRANDNAME'] = $brand_name;
        }
        if (!empty($logo_img)) {
            $this->style['LOGOIMG'] = $logo_img /* 190x60 .gif, .jpg, or .png */
            ;
        }
        if (!empty($landing)) {
            $this->style['LANDINGPAGE'] = ($landing == 'Login') ? 'Login' : 'Billing';
        }
        if (!empty($theme_color)) {
            $this->style['CARTBORDERCOLOR'] = $theme_color;
        }
        if (!empty($customer_service_no)) {
            $this->style['CUSTOMERSERVICENUMBER'] = $customer_service_no;
        }
        return true;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/locale_codes/
    */
    public function set_locale($country_code)
    {
        $locale = array(
            'AL' => 'en_US', 'AR' => 'es_XC', 'AU' => 'en_AU', 'AT' => 'de_DE', 'BE' => 'en_US', 'BZ' => 'es_XC', 'BJ' => 'fr_XC', 'BT' => 'en_US',
            'BO' => 'es_XC', 'BA' => 'en_US', 'BR' => 'pt_BR', 'BN' => 'en_US', 'BG' => 'en_US', 'BF' => 'fr_XC', 'BI' => 'fr_XC', 'KH' => 'en_US',
            'CA' => 'en_US', 'TD' => 'fr_XC', 'CL' => 'es_XC', 'CN' => 'zh_CN', 'C2' => 'en_US', 'C2' => 'en_US', 'CO' => 'es_XC', 'KM' => 'fr_XC',
            'CR' => 'es_XC', 'HR' => 'en_US', 'CY' => 'en_US', 'CD' => 'fr_XC', 'DK' => 'da_DK', 'DJ' => 'fr_XC', 'DO' => 'es_XC', 'EC' => 'es_XC',
            'SV' => 'es_XC', 'EE' => 'en_US', 'FO' => 'da_DK', 'FR' => 'fr_FR', 'GA' => 'fr_XC', 'DE' => 'de_DE', 'GL' => 'da_DK', 'GT' => 'es_XC',
            'GN' => 'fr_XC', 'HN' => 'es_XC', 'HK' => 'zh_HK', 'IS' => 'en_US', 'IN' => 'en_GB', 'ID' => 'id_ID', 'IL' => 'en_US', 'JM' => 'es_XC',
            'JP' => 'ja_JP', 'KR' => 'en_US', 'LA' => 'en_US', 'LV' => 'en_US', 'LT' => 'en_US', 'LU' => 'en_US', 'MY' => 'en_US', 'MV' => 'en_US',
            'ML' => 'fr_XC', 'MT' => 'en_US', 'MX' => 'es_XC', 'FM' => 'en_US', 'MN' => 'en_US', 'NP' => 'en_US', 'NL' => 'nl_NL', 'NL' => 'nl_NL',
            'NI' => 'es_XC', 'NE' => 'fr_XC', 'NO' => 'no_NO', 'PA' => 'es_XC', 'PE' => 'es_XC', 'PH' => 'en_US', 'PL' => 'pl_PL', 'PT' => 'en_US',
            'RU' => 'ru_RU', 'RW' => 'fr_XC', 'WS' => 'en_US', 'SN' => 'fr_XC', 'SC' => 'fr_XC', 'SG' => 'en_GB', 'ES' => 'es_ES', 'LK' => 'en_US',
            'SE' => 'sv_SE', 'CH' => 'de_DE', 'TW' => 'zh_TW', 'TH' => 'th_TH', 'TG' => 'fr_XC', 'TO' => 'en_US', 'TR' => 'tr_TR', 'UA' => 'en_US',
            'GB' => 'en_GB', 'US' => 'en_US', 'UY' => 'es_XC', 'VE' => 'es_XC', 'VN' => 'en_US'
        );
        if (!isset($locale[$country_code])) {
            return false;
        }
        $this->locale_code = $locale[$country_code];
        return true;
    }

    public function set_shipping_address($addr)
    {
        if (!is_array($addr)) {
            return false;
        }
        $this->require_shipping = true;
        $this->shipping_address = $addr;
    }

    public function add_shipping_fee($price)
    {
        $this->require_shipping = true;
        $this->shipping_fee = $price;
    }

    public function add_shipping_discount($price)
    {
        $this->require_shipping = true;
        $this->shipping_discount = $price;
    }

    /**********************************************
     *
     * Objects
     **********************************************/

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/#id09BHC0UG0RO
    */
    public function item_object($name, $quantity, $single_price, $currency, $type = 'Physical', $tax = '', $description = '')
    {
        if (!in_array($currency, $this->currencys) or empty($name) or empty($quantity) or empty($single_price) or empty($currency) or empty($type)) {
            $this->error_msg = "Invalid item parameter.";
            return false;
        }
        $item_length = count($this->item_list);
        $item = array(
            'L_PAYMENTREQUEST_0_ITEMCATEGORY' . $item_length => 'Physical',
            'L_PAYMENTREQUEST_0_NAME' . $item_length => $name,
            'L_PAYMENTREQUEST_0_AMT' . $item_length => $single_price,
            'L_PAYMENTREQUEST_0_QTY' . $item_length => $quantity,
            'L_PAYMENTREQUEST_0_CURRENCYCODE' . $item_length => $currency,
        );
        if ($type == 'Physical') {
            $this->require_shipping = true;
            $item['L_PAYMENTREQUEST_n_ITEMCATEGORY' . $item_length] = 'Physical';
        } else {
            $item['L_PAYMENTREQUEST_n_ITEMCATEGORY' . $item_length] = 'Digital';
        }
        if (!empty($description)) {
            $item['L_PAYMENTREQUEST_0_DESC' . $item_length] = $description;
        }
        if (!empty($tax)) {
            $item['L_PAYMENTREQUEST_0_TAXAMT' . $item_length] = $this->to_float($tax);
        }
        return $item;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/#id09BHCI005L7
    */

    public function address_object($recipient_name, $address_line_1, $address_line_2, $city, $country_code, $postal_code, $state_name = '', $phone = '')
    {
        if (empty($recipient_name) or empty($address_line_1) or empty($address_line_2) or empty($city) or empty($country_code) or empty($postal_code)) {
            $this->error_msg = "Invalid address parameter.";
            return false;
        }
        $address = array(
            'PAYMENTREQUEST_0_SHIPTONAME' => $recipient_name,
            'PAYMENTREQUEST_0_SHIPTOSTREET' => $address_line_1,
            'PAYMENTREQUEST_0_SHIPTOSTREET2' => $address_line_2,
            'PAYMENTREQUEST_0_SHIPTOCITY' => $city,
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => $country_code,
            'PAYMENTREQUEST_0_SHIPTOZIP' => $postal_code,
        );
        if (!empty($state_name)) {
            $address['PAYMENTREQUEST_0_SHIPTOSTATE'] = $state_name;
        }
        if (!empty($phone)) {
            $address['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $phone;
        }
        return $address;
    }

    /*
        Reference
            https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/#id09BHCD0707U
    */

    private function to_int($string_number)
    {
        return (int)str_replace(',', '', $string_number);
    }

}