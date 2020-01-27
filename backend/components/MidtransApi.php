<?php


namespace backend\components;


use Exception;
use yii\base\Component;

class MidtransApi extends Component {
    public $productionClientKey = 'Mid-client-pu0GPZ4fOvqfP_LH';
    public $productionServerKey = 'Mid-server-tPyCM0DJWYoSLy322I5aOmEL';
    public $sandboxClientKey = 'SB-Mid-client-oTFaGOfccpm4C_67';
    public $sandboxServerKey = 'SB-Mid-server-fnmHkteX-xc3cwOFa-obrxpQ';
    public $mode = 'dev';

    const MODE_DEV = 'dev';
    const MODE_PROD = 'prod';

    private function _curl($url, $data = array(), $method = 'POST') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //skipping SSL_CERT for host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //skipping SSL_CERT
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); //ignoring server redirect

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type: application/json",
            "Accept: application/json",
            "Authorization: Basic " . base64_encode($this->getServerKey() . ":")
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        $isi = curl_exec($ch);

        return $isi;
    }

    public function getServerKey() {
        return $this->getIsProduction() ? $this->productionServerKey : $this->sandboxServerKey;
    }

    public function getClientKey() {
        return $this->getIsProduction() ? $this->productionClientKey : $this->sandboxClientKey;
    }

    public function getSnapBaseUrl() {
        return $this->getIsProduction() ? 'https://app.midtrans.com/snap/v1/' : 'https://app.sandbox.midtrans.com/snap/v1/';
    }

    public function getApiBaseUrl() {
        return $this->getIsProduction() ? 'https://api.midtrans.com/v2/' : 'https://api.sandbox.midtrans.com/v2/';
    }

    public function getSnapJs() {
        return $this->getIsProduction() ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function getStatus($invoice){
        return json_decode($this->_curl($this->getApiBaseUrl(). $invoice .'/status',[], 'GET'));
    }

    public function createToken($invoice, $amount, $custommerDetails = []) {
        $transaction = array(
            'transaction_details' => array(
                'order_id' => $invoice,
                'gross_amount' => $amount // no decimal allowed
            ),
            'customer_details' => $custommerDetails
        );

        return $this->_curl($this->getSnapBaseUrl() . '/transactions', $transaction, 'POST');
    }

    public function getIsProduction() {
        return $this->mode == self::MODE_PROD;
    }

}
