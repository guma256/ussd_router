<?php

class Model {

    function __construct() {

        $this->format = new Formatclass();
        $this->log = new Logs();
       $this->redis =  new Redisclass();
        Session::start();
    }

    function SessionExists($req_params){

      return $this->redis->KeyExists($req_params['sessionId']);
    }

    function GetSessionRecords($sessionId){
      $route_data = $this->redis->GetKeyRecords($sessionId);
   return $route_data;
    }

    function GetShortCodeData($shortode,$operator){
           $route_data = $this->getOperatorRoute($shortode,$operator);
     return $route_data;
    }



    function  getOperatorRoute($shortode,$operator){
        $shortode_data =  array();
       foreach (SHORT_CODES as $key => $value) {
         if($value['shortcode']==$shortode){
           $shortode_data['route_url'] = $value[$operator.'_url'];
           $shortode_data['shortcode'] = $value['shortcode'];
        return $shortode_data ;
         }
       }
            //Get Default
       foreach (SHORT_CODES as $key => $value) {
         if(isset($value['default'])=='yes'){
           $shortode_data['route_url'] = $value[$operator.'_url'];
           $shortode_data['shortcode'] = $value['shortcode'];
        return $shortode_data;
         }
       }

     return 0;
    }

    function SaveNewSession($data) {
  //print_r($data);die();
            //$postData['session_id'] = $data['sessionId'];
            //$postData['session_date'] = date('Y-m-d H:i:s');
            $postData['msisdn'] = $data['msisdn'];
            $postData['url'] = $data['url'];
            $postData['shortcode'] = $data['shortcode'];
              //print_r($postData);die();
            $this->redis->StoreArrayRecords($data['sessionId'], $postData);

    }

    function ProcessCleanSession($req_params) {

      $this->redis->DeleteKey($req_params['sessionId']);
    }

    function WriteResponseXML($array) {
        // create simpleXML object
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><response></response>");
        $this->ArrayToXML($array, $xml);
        return $xml->asXML();
    }

    function ArrayToXML($array, &$xml) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild("$key");
                    $this->ArrayToXML($value, $subnode);
                } else {
                    $this->ArrayToXML($value, $xml);
                }
            } else {
              //  $xml->addChild("$key", "$value");
                $xml->addChild("$key",htmlspecialchars($value));
              //  $xml->$key = $value;
            }
        }
    }


    function SendGetByCURL($url,$req_params,$extra_headers=array()) {

         $this->log->ExeLog($req_params,'Model::SendGetByCURL Sending  To ' . $url, 2);
         $ch = curl_init();
         if(!empty($extra_headers)){
         curl_setopt($ch, CURLOPT_HTTPHEADER, $extra_headers);
         }
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

         $content = curl_exec($ch);
         if (!curl_errno($ch)) {
             $info = curl_getinfo($ch);
             $log = 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
         } else {
             $log = 'Curl error: ' . curl_error($ch);
         }
        //$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         $this->log->ExeLog($req_params,'Model::SendGetByCURL Returning ' . $log, 2);

 	  $this->log->ExeLog($req_params,'Model::SendGetByCURL response content '. var_export($content, true), 2);
         return $content;
     }

}
?>
