<?php

class Formatclass {

    function __construct() {
           $this->log = new Logs();
    }

    public $_match_up_array = array(
        'type' => 'requesttype',
        'methodName' => 'requesttype',
        'username' => 'username',
        'password' => 'password',
        'timestamp' => 'timestamp',
        'msisdn' => 'msisdn',
        'sessionId' => 'sessionId',
        'session' => 'sessionId',
        'sessionid' => 'sessionId',
        'freeflowState' => 'state',
        'shortcode' => 'subscriberInput',
        'statusCode' => 'statuscode',
        'applicationResponse' => 'msg_response',
        'mode' => 'mode',
        'response' => 'subscriberInput',
        'amount' => 'amount',
        'language' => 'language',
        'name' => 'name',
        'last_name' => 'last_name',
        'first_name' => 'first_name',
        'string' => 'string',
        'newRequest' => 'newRequest',
        'clean' => 'clean',
        'subscriberInput' => 'subscriberInput',
        'transactionId' => 'transactionId',
        'new_request' => 'new_request',  //Airtel ussed
        'input' => 'subscriberInput',

    );


    function ParseXMLFromURL($url) {
        $xmlp = simplexml_load_file($url);
        $p_array = $this->ObjectToArray($xmlp);
        return $p_array;
    }

    function ParseXMLResponse($xml_post, $level = false) {


        if ($level) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($xml_post);
            libxml_clear_errors();
            $xmln = $doc->saveXML($doc->documentElement);
        } else {
            $xmln = $xml_post;
        }

        $xmlp = simplexml_load_string($xmln);
        $p_array = $this->ObjectToArray($xmlp);

        $request_array = $this->ArrayFlattener($p_array);
         if(is_array($request_array)){
        $standard_array = $this->Standardize($request_array);
        return $standard_array;
       }else{
        return $request_array;
      }
    }

    function ParseXMLRequest($xml_post, $level = false) {


        if ($level) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($xml_post);
            libxml_clear_errors();
            $xmln = $doc->saveXML($doc->documentElement);
        } else {
            $xmln = $xml_post;
        }

        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c','&'=>' n ',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y','Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u','ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T');
         $xmln = strtr($xmln, $unwanted_array);
        $xmlp = simplexml_load_string($xmln);
        $p_array = $this->ObjectToArray($xmlp);

        $request_array = $this->ArrayFlattener($p_array);
         if(is_array($request_array)){
        $standard_array = $this->Standardize($request_array);
        return $standard_array;
       }else{
        return $request_array;
      }
    }

    function Standardize($data_array) {
        //Convert to Single
        $result_array = array();
        foreach ($data_array as $key => $value) {
            if(array_key_exists($key,$this->_match_up_array)){
            $standard_key = $this->_match_up_array[$key];
            if (!empty($standard_key)) {
                $result_array[$standard_key] = $value;
            }
          }
        }
        return $result_array;
    }

    function ObjectToArray($obj) {
        if (!is_array($obj) && !is_object($obj))
            return $obj;
        if (is_object($obj))
            $obj = get_object_vars($obj);
        return array_map(__METHOD__, $obj);
    }

    function ArrayFlattener($array) {
        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->ArrayFlattener($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }




}
