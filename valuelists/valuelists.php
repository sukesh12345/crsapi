<?php

namespace valuelists;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT ; 

use config as dbconnection;


class valueclass{
    public function stateslist($request,$response){
        $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindAllCommand('states');
    $result=$findCommand->execute(); 
    if (\FileMaker::isError($result)) {
        if ($result->code = 401) {
        $findError = 'There are no Records that match that request: '. ' (' .
        $result->code . ')';
        } else {
        $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->code
        . ')';
        }
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["succes"=>false,"message"=>$findError]);
        }   
        $count=count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedstates[$i]=$result->getRecords()[$i]->_impl->_fields['statesname'][0];
        }            
        if($count>=1){
            $newresponse = $response->withStatus(200);
            //print_r($ph);
            return $newresponse->withJson(['success'=>true, 'data'=>$fetchedstates,'count'=>$count]);
        } 
    }

    public function skilllist($request,$response){
        $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindAllCommand('TagMaster');
    $result=$findCommand->execute(); 
    if (\FileMaker::isError($result)) {
        if ($result->code = 401) {
        $findError = 'There are no Records that match that request: '. ' (' .
        $result->code . ')';
        } else {
        $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->code
        . ')';
        }
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["succes"=>false,"message"=>$findError]);
        }   
        $count=count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedskills[$i]=$result->getRecords()[$i]->_impl->_fields['TagName'][0];
        }            
        if($count>=1){
            $newresponse = $response->withStatus(200);
            //print_r($ph);
            return $newresponse->withJson(['success'=>true, 'data'=>$fetchedskills,'count'=>$count]);
        } 
    }

    public function companies($request,$response,$args){
        $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindAllCommand('Job');
    $result = $findCommand->execute();
     if (\FileMaker::isError($result)) {
        if ($result->code = 401) {
        $findError = 'There are no Records that match that request: '. ' (' .
        $result->code . ')';
        } else {
        $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->code
        . ')';
        }
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false , "message"=>$findError]);
        } 
    else{
        $count=count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedcompanies[$i]=$result->getRecords()[$i]->_impl->_fields['Company'][0];
        }        
        $fetchedcompanies = array_values(array_unique($fetchedcompanies));    
        if($count>=1){
            $newresponse = $response->withStatus(200);
            //print_r($ph);
            return $newresponse->withJson(['success'=>true, 'data'=>$fetchedcompanies,'count'=>$count]);
        } 
    }
    }
}