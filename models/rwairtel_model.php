<?php

class RWAirtel_Model extends Router {

    function __construct() {
        parent::__construct();
    }


    function ProcessRequest($orignal_req, $req_params) {

        $operator = 'airtelrw';
        $req_params['operator']='airtelrw';

        $req_response = $this->ManageHttpRequestSession($req_params,$orignal_req);

        $this->log->ExeLog($req_params, 'RWAirtel_Model::Handler ManageRequestSession Returning Status ' . var_export($req_response,true), 2);

        return $req_response;
    }


        function ManageHttpRequestSession($req_params,$request){

         $check =$this->GetSessionRecords($req_params['sessionId']);
         //print_r($check);die();
         $flag =0;
         if(empty($check)){
          $param_array = explode("*", $req_params['subscriberInput']);
          if(isset($param_array[1])&&strlen($param_array[1])>1){
          $shortode = $param_array[0]."*".$param_array[1];
          $flag = $shortode;
        }else{
          $shortode = $param_array[0];
        }
           $sc_data = $this->GetShortCodeData($shortode,$req_params['operator']);

           if(isset($param_array[2])&&$sc_data['shortcode']==$flag){
             array_shift($param_array);
             $input= array_shift($param_array);
             foreach ($param_array as $key => $value) {
               $input.="*".$value;
             }
           $request = str_replace($req_params['subscriberInput'],$input , $request);
           }

           $req_params['url']=$sc_data['route_url'];
           $req_params['shortcode']=$sc_data['shortcode'];
                //print_r($req_params);die();
           $this->SaveNewSession($req_params);
         $send_request =$this->SendHTTPByCURL($req_params,$sc_data['route_url'],$request);

         }else{

         $send_request =$this->SendHTTPByCURL($req_params,$check['url'],$request);

           }

        return $send_request;
        }



	function FormatRequest($query){
	   $request_r=urldecode($query);

	   $array = explode("&", $request_r);
	  $transdata=array();

		foreach ($array as $item) {
		$values=explode("=", $item);
		   $fkey = strtolower($values[0]);
		 $transdata["$fkey"] = $values[1];

		}

	 $reqdata=$this->format->Standardize($transdata);

	return $reqdata;
	}
}
