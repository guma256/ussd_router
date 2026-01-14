<?php

class Router extends Model {

    function __construct() {
        parent::__construct();
        $this->log = new Logs();
    }


  function ManageRequestSession($req_params,$request){
    $shortode=$req_params['subscriberInput'];
   $check =$this->SessionExists($req_params);
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
      // print_r($shortode);die();
     $sc_data = $this->GetShortCodeData($shortode,$req_params['operator']);
     //print_r($sc_data);die();
     if($sc_data['shortcode']==$flag&&isset($param_array[2])){
       array_shift($param_array);
       $input= array_shift($param_array);
       foreach ($param_array as $key => $value) {
         $input.="*".$value;
       }
     $request = str_replace($req_params['subscriberInput'],$input , $request);
     }
     //print_r($sc_data);die();
     $req_params['url']=$sc_data['route_url'];
     $req_params['shortcode']=$sc_data['shortcode'];

     $saved =$this->SaveNewSession($req_params);
   $send_request =$this->SendExternalRequest($req_params,$request,$sc_data['route_url']);

   }else{
    $session_data =$this->GetSessionRecords($req_params['sessionId']);
      //print_r($session_data);die();
$this->log->ExeLog($req_params, 'USSD::ManageRequestSession GetSessionRecords  Returned records ' .var_export($session_data,true) ,3);

   $send_request =$this->SendExternalRequest($req_params,$request,$session_data['url']);
     }

   if(empty($send_request)&&$req_params['requesttype']=='cleanup'){

     $this->log->ExeLog($req_params, 'USSD::ManageRequestSession SendExternalRequest  Closing session ID '.$req_params['sessionId']." for msisdn ".$req_params['msisdn'] ,3);
     exit();
   }else{
     $resp =$this->format->ParseXMLResponse($send_request);
     $array = $this->PrePareRespArray($req_params,$resp);
    }
  return $array;
  }

  function ManageSessionCleanUpRequest($req_params,$request){

   $check =$this->GetSessionRecords($req_params['sessionId']);
   if(empty($check)){
      exit();
   }else{
     $this->ProcessCleanSession($req_params);
   $send_request =$this->SendExternalRequest($req_params,$request,$check['url']);
     }
   exit();
  }



    function SendExternalRequest($req_params, $request,$url) {

       $this->log->ExeLog($req_params, 'USSD::SendExternalRequest  Request Data ' . var_export($request, true), 2);

        $this->log->ExeLog($req_params, 'USSD::SendExternalRequest Preparing to send Request ' . $request . ' To ' . $url, 2);
        $result = $this->SendJSONByCURL($req_params,$url,$request);

        $this->log->ExeLog($req_params, 'USSD::SendExternalRequest Response From USSD APP ' . var_export($result, true), 2);
        return $result;
    }


    public function _isValidXML($xml) {
        $doc = @simplexml_load_string($xml);
        if ($doc) {
            return true; //this is valid
        } else {
            return false; //this is not valid
        }
    }



       function SendJSONByCURL($req_params,$url,$request) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $content = curl_exec($ch);
            if (!curl_errno($ch)) {
                $info = curl_getinfo($ch);
                $log = 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
            } else {
                $log = 'Curl error: ' . curl_error($ch);
            }
            $this->log->ExeLog($req_params,'Router::SendJSONByCURL Returning ' . $log, 2);

    	  $this->log->ExeLog($req_params,'Router::SendJSONByCURL response content '. var_export($content, true), 2);
            return $content;
        }


       function SendHTTPByCURL($req_params,$url,$request) {
            $url =  $url.'?'.$request;
            $ch = curl_init( $url);
            curl_setopt($ch, CURLOPT_URL, $url.'?'.$request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            //for debug only!
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($ch);
             $error = curl_error($ch);
             $result = array( 'header' => '',
                              'body' => '',
                              'curl_error' => '',
                              'http_code' => '',
                              'last_url' => '');
             if ( $error != "" )
             {
                 $result['curl_error'] = $error;
                 return $result;
             }

             $headerSize = curl_getinfo( $ch , CURLINFO_HEADER_SIZE );
             $headerStr = substr( $content , 0 , $headerSize );
             $bodyStr = substr( $content , $headerSize );
            $header=$this->headersToArray($headerStr);
            //print_r($bodyStr);die();
             $result['body'] = $bodyStr;
             $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
             $result['header'] = $header;
             $this->log->ExeLog($req_params,'Router::SendHTTPByCURL response content '. var_export($result, true), 2);

             return $result;
             }


        function PrePareRespArray($params, $resp) {
            $response_array = array(
                'msisdn' => $params['msisdn'],
                'sessionid' => $params['sessionId'],
              //  'transactionid' => $params['transactionId'],
                'freeflow' => array(
                    'freeflowState' =>  isset($resp['state'])? $resp['state']:'FB'
                ),
                'applicationResponse' => isset($resp['msg_response'])? $resp['msg_response']:'Request Failed' .PHP_EOL.'Try again Later Ibyo musabye ntibibashije kuboneka mwongere mu kanya.',
            );
            return $response_array;
        }

        function headersToArray( $str )
        {
            $headers = array();
            $headersTmpArray = explode( "\r\n" , $str );
            for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
            {
                // we dont care about the two \r\n lines at the end of the headers
                if ( strlen( $headersTmpArray[$i] ) > 0 )
                {
                    // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
                    if ( strpos( $headersTmpArray[$i] , ":" ) )
                    {
                        $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                        $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                        $headers[$headerName] = $headerValue;
                    }
                }
            }
            return $headers;
        }

        function ValidateAllowedCharacters($str) {
          return !preg_match(ALLOWED_CHARS, $str) > 0;
      }
      

}

?>
