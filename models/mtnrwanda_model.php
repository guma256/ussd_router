<?php

class Mtnrwanda_Model extends Router {

    function __construct() {
        parent::__construct();
    }

    function ProcessRequest($orignal_req, $req_params) {
        $req_params['operator'] = 'mtnrwanda';
        //print_r($req_params);die();
   if($this->ValidateAllowedCharacters($req_params['subscriberInput'])){
        if(MAINTENANCE=='1'){
       $req_params['state'] = 'FB';
       $req_params['msg_response'] = MAINTENANCE_MESSAGE;
       $req_response = $this->PrePareRespArray($req_params,$req_params);
        }else{
        $req_response = $this->ManageRequestSession($req_params,$orignal_req);
   //print_r($response);die();
        }
    }else{
        $req_params['msg_response']='Dear customer, What you entered is not allowed.'.PHP_EOL.'Nshuti mukiriya,Ibyo winjiye ntibyemewe.';
        $req_params['state']= 'FB';
        $req_response = $this->PrePareRespArray($req_params,$req_params);
    }
    $this->log->ExeLog($req_params, 'Mtnrwanda_Model::Handler ManageRequestSession Returning Status ' . var_export($req_response, true), 3);
  
    $response = $this->WriteResponseXML($req_response);

        return $response;
    }

    function ProcessCleanUpRequest($orignal_req, $req_params) {

        //$operator = $this->GetOperator('mtn');
        $req_params['operator'] = 'mtnrwanda';
        $req_response = $this->ManageSessionCleanUpRequest($req_params,$orignal_req);

        $response = $this->WriteResponseXML($req_response);
   //print_r($response);die();
        $this->log->ExeLog($req_params, 'Mtnrwanda_Model::Handler ProcessCleanUpRequest Returning Status ' . $response, 2);

        return $response;
    }


      function InterpreteRequest($xml_post) {
      $this->log->LogXML('mtn_rw','pull' ,$xml_post);
            $standard_array = $this->format->ParseXMLRequest($xml_post);
            return $standard_array;
      }



}
