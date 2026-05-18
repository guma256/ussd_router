<?php

class Redisclass {

    function __construct() {
     $this->redis = new Redis();
     try {
         $this->redis->pconnect(REDIS_HOST, REDIS_PORT);
         //$this->redis->auth(REDIS_PASSWORD);
     } catch (Exception $e) {
         // handle error silently
     }
    }


    function connect() {
        // No-op
    }


        public function DisConnect() {
            // No-op
        }

        public function DeleteKey($key) {
       $reponse =$this->redis->del($key);
        return $reponse;
        }


        function KeyExists($key){
         $response = $this->redis->exists($key);
        return  $response;
        }


       function StoreNameWitValue($key,$name,$value){
         $response = $this->redis->HSET($key,$name,$value);
          return  $response;
       }

       function GetKeyRecords($key){
         $response = $this->redis->HGETALL($key);
          return  $response;
       }

       function StoreArrayRecords($key,$array=array()){
         $response =  $this->redis->HMSET($key,$array);
             $this->redis->expire($key,SESSION_ID_EXP);
          return  $response;
       }

       function ExpireRecords($key,$seconds=190){

         return $this->redis->expire($key,$seconds);
       }




}

?>
