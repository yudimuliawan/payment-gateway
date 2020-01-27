<?php


namespace backend\components;


use yii\base\Component;

class XenditApi extends Component {
    public $productionApiKey = '';
    public $sandboxApiKey = '';
    public $mode = 'dev';

    const MODE_DEV = 'dev';
    const MODE_PROD = 'prod';

    private function _curl($url, $data = array(), $method = 'POST') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //skipping SSL_CERT for host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //skipping SSL_CERT
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); //ignoring server redirect
        curl_setopt($ch, CURLOPT_USERPWD, $this->getApiKey().":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type: application/json",
            "Accept: application/json",
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        $isi = curl_exec($ch);

        return json_decode($isi);
    }

    public function create($invoice, $amount, $phone){
        return $this->_curl($this->getApiUrl(),[
            'external_id' => $invoice,
            'amount' => $amount,
            'phone' => $phone,
            'ewallet_type' => 'OVO'
        ]);
    }

    public function getStatus($invoice){
        \Yii::error($invoice);
        return $this->_curl($this->getApiUrl().'?external_id='.$invoice.'&ewallet_type=OVO',[], 'GET');
    }

    public function getApiKey(){
        return $this->getIsProduction() ? $this->productionApiKey : $this->sandboxApiKey;
    }

    public function getApiUrl() {
        return 'https://api.xendit.co/ewallets/';
    }

    public function getIsProduction() {
        return $this->mode == self::MODE_PROD;
    }
}
