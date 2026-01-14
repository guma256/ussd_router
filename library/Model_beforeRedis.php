<?php

class Model_Before {

    function __construct() {

        $this->format = new Formatclass();
        $this->log = new Logs();
        $this->db = new Database();
        Session::start();
    }

    function SessionExists($req_params){

      return $this->db->SelectData("SELECT * FROM session_records WHERE msisdn='".$req_params['msisdn']."' AND  session_id='".$req_params['sessionId']."' AND active_session='active' ");
    }

    function GetOperator($search){

      return $this->db->SelectData("SELECT * FROM mobile_operators WHERE search_key='".$search."'  ");
    }

    function GetShortCodeData($shortode,$operator){
        // print_r($shortode);die();
      $resp= $this->db->SelectData("SELECT * FROM short_code_routes r JOIN short_codes s ON r.short_code_id=s.record_id WHERE s.shortcode ='".$shortode."' AND r.operator_id='".$operator."' AND s.status='active' ");
      if(empty($resp)){
        $resp= $this->db->SelectData("SELECT * FROM short_code_routes r JOIN short_codes s ON r.short_code_id=s.record_id  WHERE  s.is_default='yes' AND r.operator_id='".$operator."' ");
      }
     return $resp[0];
    }



    function SaveNewSession($data) {

            $postData['session_id'] = $data['sessionId'];
            $postData['session_date'] = date('Y-m-d H:i:s');
            $postData['msisdn'] = $data['msisdn'];
            $postData['url'] = $data['url'];
            $postData['short_code_id'] = $data['shortcode'];
              //print_r($postData);die();
            $this->db->InsertData('session_records', $postData);

    }

    function ProcessCleanSession($req_params) {
        $postData = array();
        $postData['active_session'] = 'closed';
        $postData['closed_session_at'] = date('Y-m-d H:i:s');
        $this->db->UpdateData('session_records', $postData, "session_id = {$req_params['sessionId']}");
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
