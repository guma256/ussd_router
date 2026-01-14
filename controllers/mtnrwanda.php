<?php

class Mtnrwanda extends Controller {

    function __construct() {
        parent::__construct();
    }


        function Index() {

            $request = file_get_contents('php://input');
            if (empty($request)) {
                echo "Invalid router Access";
                echo "<br/>";
                echo "<br/>";
                 die();
            } else {
            //var_dump($this->model->_isValidXML($request));    die();
            $standard_array = $this->model->InterpreteRequest($request);

            if (strtolower($standard_array['requesttype']) == 'cleanup') {
                 $this->model->ProcessCleanUpRequest($request,$standard_array);
                 exit();
             }else{
                $standard_array['operator'] = 'mtnrwanda';
                    $response_xml = $this->model->ProcessRequest($request, $standard_array);
                    header('Content-Type: application/xml; charset=UTF-8');
                    echo $response_xml;
                }


            }
        }


}
