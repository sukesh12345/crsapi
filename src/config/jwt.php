<?php

namespace config;

use \Firebase\JWT\JWT as token; 

class jwt 
{
    private $secretkey;

    public function __construct() {
        $this->secretkey = 'campusrecruitmentsystem';
    }

    public function jwttokenencryption( $status) 
    {   
      //$payload = json_encode(array('status' => $status));
      $payload = json_encode(array('status' => $status));
      $encoded = token::encode($payload, $this->secretkey, 'HS256');
        return $encoded;
    }
    public function jwttokendecryption($token) {  
        $obj = [];
        $error = false;
       try {
            $decoded = token::decode($token, $this->secretkey, array('HS256'));
        } catch(\Exception $e) {
            $result['message']=$e->getMessage();
            $error = true;
        } finally {
            if($error) {
                $obj["verification"]= "failed";
                return json_encode($obj);   
            } else {
                $obj["verification"] = "passed";
                $data = json_decode($decoded);        
                $obj["status"] = $data->status;
                return json_encode($obj);
            }        
        }      
    }
}



?>