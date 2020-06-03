<?php

namespace jobs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT ; 

use config as dbconnection;



class jobsclass{



public function getjobs($request,$response,$args){
    $jwt = new \config\jwt();
            
            if( $request->hasHeader("Authorization") == false) {
                $newresponse = $response->withStatus(400);
                return $newresponse->withJson(["message"=>"required jwt token is not recieved"]);
            }
            $header = $request->getHeader("Authorization");
            $vars = $header[0];
            $token = json_decode($jwt->jwttokendecryption($vars));
            if( $token->verification == "failed") {
                // header("location: index.html");
                $newresponse = $response->withStatus(401);
                return $newresponse->withJson(["message"=>"you are not authorized"]);
            }
            $Id = $args['id'];
            $dbobj = new dbconnection\dbconnection();
            $fm = $dbobj->connect();
            $findCommand = $fm->newFindCommand('Job');
            $findCommand->addFindCriterion('__kf_UserId', $Id);
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
                return $newresponse->withJson(["success"=>false ,"message"=>$findError]);
                }
                $count=count($result->getRecords());
                for($i=0;$i<$count;$i++){
                    $fetchedjobdata[$i]=$result->getRecords()[$i]->_impl->_fields;
                }            
            if($count>=1){
                $newresponse = $response->withStatus(200);
                //print_r($ph);
                return $newresponse->withJson(['success'=>true, 'data'=>$fetchedjobdata,'count'=>$count]);
            } else {
                $newresponse =  $response->withStatus(404);
                return $newresponse->withJson(["success"=>false]);
            }
}



public function viewjobapplications($request,$response,$args){
    $jwt = new \config\jwt();
    $vars = json_decode($request->getBody());
    if( $request->hasHeader("Authorization") == false) {
        $newresponse = $response->withStatus(400);
        return $newresponse->withJson(["message"=>"required jwt token is not recieved"]);
    }
    $header = $request->getHeader("Authorization");
    $vars =$header[0];
    $token = json_decode($jwt->jwttokendecryption($vars));
    if( $token->verification == "failed") {
        $newresponse = $response->withStatus(401);
        return $newresponse->withJson(["message"=>"you are not authorized"]);
    }
    $id = $args['id'];
    $jobid = $args['jobid'];
    $status = $args['status'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindCommand('Application');
    $findCommand->addFindCriterion('__kf_JobId', $jobid);
    $findCommand->addFindCriterion('Status',$status);
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
        return $newresponse->withJson(["success"=>false ,"message"=>$findError]);
        }
        $count=count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedapplicationdata[$i]=$result->getRecords()[$i]->_impl->_fields['__kf_UserId'][0];
        }
        for($i=0;$i<$count;$i++)
        {
            $findCommand = $fm->newFindCommand('User');
            $findCommand->addFindCriterion('___kp_UserId',$fetchedapplicationdata[$i]);
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
            return $newresponse->withJson(["success"=>false]);
            }   
            $fetcheduserdata[$i]=$result->getRecords()[0]->_impl->_fields;
            $__kf_Id=$result->getRecords()[0]->_impl->_fields['___kp_UserId'][0];
            $findCommand = $fm->newFindCommand('Address');
            $findCommand->addFindCriterion('__kf_Id', $__kf_Id);
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
            return $newresponse->withJson(["success"=>false ,"message"=>$findError]);
        }   
        $fetcheduseraddress[$i]=$result->getRecords()[0]->_impl->_fields;
        $fetchedusercompletedata[$i]=$fetcheduserdata[$i]+$fetcheduseraddress[$i];
        }
        if($count>=1){
        $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, 'data'=>$fetchedusercompletedata,'count'=>$count]);
    } else {
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false]);
    }  
}


public function retrievejobpost($request,$response,$args){
    $jwt = new \config\jwt();
    $vars = json_decode($request->getBody());
    if( $request->hasHeader("Authorization") == false) {
        $newresponse = $response->withStatus(400);
        return $newresponse->withJson(["message"=>"required jwt token is not recieved"]);
    }
    $header = $request->getHeader("Authorization");
    $vars =$header[0];
    $token = json_decode($jwt->jwttokendecryption($vars));
    if( $token->verification == "failed") {
        $newresponse = $response->withStatus(401);
        return $newresponse->withJson(["message"=>"you are not authorized"]);
    }
    $jobid = $args['jobid'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindCommand('Job');
    $findCommand->addFindCriterion('___kp_JobId',$jobid);
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
    $deletecommand=$result->getRecords()[0];
    $deletecommand->delete();
    if(count($result->getRecords())==1){
        $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true,'message'=>'record deleted successfully']);
    } else {
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false]);
    }
}

}