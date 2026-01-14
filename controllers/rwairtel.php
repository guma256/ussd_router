<?php

class RWAirtel extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index() {

    	 $todayDate = date("ymdhis");
         $transID = $todayDate.rand();
         $mytransdata = parse_url($_SERVER['REQUEST_URI']);

		$decodeddata=urldecode($mytransdata['query']);
	  //$this->model->log->ExeLog($standard_array, 'RWAirtel::Index Initial Request ' . var_export($decodeddata, true), 1);
        if (empty($mytransdata['query'])) {
                     echo 'You are not allowed on this location';
                     $size = ob_get_length();
                      header('HTTP/1.1 200 OK');
                      header('Freeflow: FC');
                    //  header('charge: N');
                      header('cpRefId: 12345');
                      header('Expires: -1');
                      header('Pragma: no-cache');
                      header('Cache-Control: max-age=0');
                      header('Content-Type: UTF-8');
                      header('Content-Length:'.$size);
        } else {

#http://127.0.0.1:8080/application_uri?userid=app1&password=app1pwd &MSIDN=919845098450&clean=clean-session&status=522
            $standard_array = $this->model->FormatRequest($decodeddata);
			      $standard_array['vendor'] = 'airtel';
          //  $response_xml = $this->model->ProcessRequest($decodeddata, $standard_array);
            //print_r($standard_array);die();
            if (isset($standard_array['clean'])&&$standard_array['clean'] == 'clean-session') {
                 header('HTTP/1.1 200 OK');
                 header('Expires: -1');
                 header('Pragma: no-cache');
                 header('Cache-Control: max-age=0');
            }else{
              $response = $this->model->ProcessRequest($decodeddata, $standard_array);
              echo $response['body'];
              $size = ob_get_length();
               header('HTTP/1.1 200 OK');
               if(isset($response['header']['Freeflow'])){
                 header('Freeflow: '.$response['header']['Freeflow']);

               }else{
                 header('Freeflow: FB');
               }
            //   header('charge: N');
               header('cpRefId: 12345');
               header('Expires: -1');
               header('Pragma: no-cache');
               header('Cache-Control: max-age=0');
               header('Content-Type: UTF-8');

            }

          /*  if ($standard_array['requesttype'] == 'USSD.END') {
                $this->model->SessionCleanUp($standard_array);
            }*/
        }
    }
}
