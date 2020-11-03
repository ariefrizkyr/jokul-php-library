<?php

namespace DOKU\Service;

use DOKU\Common\Config;

class MandiriVa {

    public static function generated($config, $params)
    {
        $words = MandiriVa::generateWords($params);

        $data = array(
            "client" => array(
                "id" => $config['client_id']
            ),
            "order" => array(
                "invoice_number" => $params['invoiceNumber'],
                "amount" => $params['amount']
            ),
            "virtual_account_info" => array(
                "expired_time" => 60,
                "reusable_status" => 'false',
            ),
            "customer" => array(
                "name" => trim($params['customerName']),
                "email" => $params['customerEmail']
            ),
            "security" => array(
                "check_sum" => $words
            )
        );

        $getUrl = Config::getBaseUrl($config['environment']);
        $url = $getUrl.'mandiri-virtual-account/v1/payment-code';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseJson = curl_exec($ch);

        curl_close($ch);

        if (is_string($responseJson)) {
            return json_decode($responseJson, true);
        } else {
            print_r($responseJson);
        }
    }

    private function generateWords($params)
    {
        $formula =
            $config['client_id'] .
            $params['customerEmail'] .
            trim($params['customerName']) .
            $params['amount'] .
            $params['invoiceNumber'] .
            60 .
            "false" .
            $config['shared_key'];

        return hash('sha256', $formula);
    }
}
?>