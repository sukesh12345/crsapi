<?php

namespace users;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT ; 

use config as dbconnection;


class userclass{
    public function register($request,$response){
        $dbobj = new dbconnection\dbconnection();   
    $conn = $dbobj->connect();
     $jwt = new \config\jwt();
    // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    $vars = json_decode($request->getBody());
    $count = 0;            
    foreach($vars as $key) {
        $count++;
    }
    if( $count<14) {
        $newresponse = $response->withStatus(400);
        return $newresponse->withJson(["message"=>"request body is not appropriate","count"=>$count]);
    }
    $userdata = array( 
        "Firstname" =>$request->getParsedBody()['firstname'],
        "Lastname" =>$request->getParsedBody()['lastname'],
        "Gender" => $request->getParsedBody()['gender'],
        "Dob" =>$request->getParsedBody()['date'],
        "Email" => $request->getParsedBody()['email'],
        "Telephone" => $request->getParsedBody()['telephone'],
        "Password" =>$jwt->jwttokenencryption( $request->getParsedBody()['password']),
        "Confirmpassword" =>$jwt->jwttokenencryption( $request->getParsedBody()['confirmpassword']),
        "Type"=>$request->getParsedBody()['type']
    );
    $stmt = $conn->createRecord('User', $userdata);
    $register = $stmt->commit();
    if (\FileMaker::isError($register)) {
        $findError = 'Find Error: '. $register->getMessage(). ' (' . $register->getCode(). ')';
        $newresponse = $response->withStatus(404);
        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
        
    }
    // $stmt = $conn->newPerformScriptCommand('User','registration email');
    // // $stmt->setScript('registration email',$request->getParsedBody()['email']); 
    // $script = $stmt->execute();
    //         if(FileMaker::isError($script)) {
    //             $findError = 'Find Error: '. $script->getMessage(). ' (' . $script->getCode(). ')';
    //             $newresponse = $response->withStatus(404);
        
    //             return $newresponse->withJson(['success'=>false, "message"=>$findError]);
                
    //         }
    $email = $request->getParsedBody()['email'];
    // $findCommand = $conn->newFindCommand('User');
    // $findCommand->addFindCriterion('Telephone', $request->getParsedBody()['telephone']);
    // $findCommand->setScript('registrationemail',$email);
   
    $findCommand = $conn->newFindCommand('User');
    $findCommand->addFindCriterion('Telephone',$request->getParsedBody()['telephone']);
    // $findCommand->addFindCriterion('Type','Student');
    // $findCommand->setScript('registrationemail',$email); 
    $result=$findCommand->execute();
    if (\FileMaker::isError($result)) {
        $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode(). ')';
        $newresponse = $response->withStatus(404);
        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
        
    }
    $id=$result->getRecords()[0]->_impl->_fields['___kp_UserId'][0];
    $address = array(
        "__kf_Id" => "$id",
        "Doorno" => $request->getParsedBody()['address1'],
        "Streetname" => $request->getParsedBody()['address2'],
        "City" =>$request->getParsedBody()['city'],
        "State" =>$request->getParsedBody()['state'],
        "Postalcode" =>$request->getParsedBody()['postalcode']
    );
    $stmt = $conn->createRecord('Address', $address);
    $add = $stmt->commit();
    if(\FileMaker::isError($add)) {
        $findError = 'Find Error: '. $add->getMessage(). ' (' . $add->getCode(). ')';
        $newresponse = $response->withStatus(404);

        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
        
    }
 
        else{
            // $runscript = $conn->newPerformScriptCommand('User', 'registrationemail', $email);
            //     $result = $runscript->execute();
            //     if(FileMaker::isError($result)) {
            //         $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode(). ')';
            //         $newresponse = $response->withStatus(404);
            
            //         return $newresponse->withJson(['success'=>false, "message"=>$findError]);
                    
            //     }
            $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, "message"=>"registered successfully"]);
        }
    
    }

    public function login(Request $request, Response $response)
    {
        $dbobj = new dbconnection\dbconnection();
        $fm = $dbobj->connect();
        $jwt = new \config\jwt();
        $vars = json_decode($request->getBody());
        if( $request->getParsedBody()['username'] == null) {
            $newresponse = $response->withStatus(401);
            return $newresponse->withJson(['status'=>false, 'message'=>'username is required ']);
        }
        if( $request->getParsedBody()['password'] == null) {
            $newresponse = $response->withStatus(401);
            return $newresponse->withJson(['status'=>false, 'message'=>'password is required']);
        }
        $username = $request->getParsedBody()['username'];
        $password = $jwt->jwttokenencryption( $request->getParsedBody()['password']);
        $findCommand = $fm->newFindCommand('User');
        $findCommand->addFindCriterion('Telephone','=='.$username);
        $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
        $findCommand->addFindCriterion('Password','=='.$password);
        $result=$findCommand->execute();   
        if (\FileMaker::isError($result)) {
            if ($result->getCode()== 401) {
            $findError = 'There are no Records that match that request: '. ' (' .
            $result->getCode() . ')';
            } else {
            $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode()
            . ')';
            }
            $newresponse =  $response->withStatus(404);
            return $newresponse->withJson(["success"=>false,"message"=>$findError]);
            }  
        $id=$result->getRecords()[0]->_impl->_fields['___kp_UserId'][0];
        if(count($result->getRecords())==1){
            // $email = $result->getRecords()[0]->_impl->_fields['Email'][0];
            // $runscript = $fm->newPerformScriptCommand('User', 'registrationemail', $email);
            //         $result = $runscript->execute();
            //         if(FileMaker::isError($result)) {
            //             $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode(). ')';
            //             $newresponse = $response->withStatus(404);
                
            //             return $newresponse->withJson(['success'=>false, "message"=>$findError]);
                        
            //         }
            $token = $jwt->jwttokenencryption( $id);
        return $response->withJson(["status"=>true,"data"=>$id , "token"=>$token]);
        } else {
            $newresponse = $response->withStatus(401);
            return $newresponse->withJson(["status"=>false, "message"=>"credentials  dosent match each other"]);
        }
    }

    public function getuser($request,$response,$args){
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
$findCommand = $fm->newFindCommand('User');
$findCommand->addFindCriterion('___kp_UserId', $Id);
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
$fetcheduserdata=$result->getRecords()[0]->_impl->_fields;
$findCommand = $fm->newFindCommand('Address');
$findCommand->addFindCriterion('__kf_Id', $Id);
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
$fetcheduseraddress=$result->getRecords()[0]->_impl->_fields;
if(count($result->getRecords())==1){
    $newresponse = $response->withStatus(200);
    //print_r($ph);
    return $newresponse->withJson(['success'=>true, 'data'=>$fetcheduserdata+$fetcheduseraddress]);
} else {
    $newresponse =  $response->withStatus(404);
    return $newresponse->withJson(["success"=>false]);
}
    }





    function updateuser($request,$response,$args){
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
            $fm = new dbconnection\dbconnection();
            $fm = $fm->connect();
            // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $vars = json_decode($request->getBody());
            $Id = $args['id'];         
            $Firstname = $request->getParsedBody()['firstname'];
            $Lastname = $request->getParsedBody()['lastname'];
            $Email = $request->getParsedBody()['email'];
            $Gender = $request->getParsedBody()['gender'];;
            $Dob = $request->getParsedBody()['date'];  
            $Doorno = $request->getParsedBody()['doorno'];
            $Streetname = $request->getParsedBody()['streetname'];
            $City = $request->getParsedBody()['city'];
            $State = $request->getParsedBody()['state'];
            $Postalcode = $request->getParsedBody()['postalcode'];
            $findCommand = $fm->newFindCommand('User');
            $findCommand->addFindCriterion('___kp_UserId', $Id);
            $result=$findCommand->execute(); 
            $findCommand=$result->getRecords()[0];
            $findCommand->setField('Firstname', $Firstname);
            $findCommand->setField('Lastname', $Lastname);
            $findCommand->setField('Gender', $Gender);
            $findCommand->setField('Dob', $Dob);
            $findCommand->setField('Email', $Email);
            $result = $findCommand->commit();
            $findCommand = $fm->newFindCommand('Address');
            $findCommand->addFindCriterion('__kf_Id', $Id);
            $result=$findCommand->execute(); 
            $findCommand=$result->getRecords()[0];
            $findCommand->setField('Doorno', $Doorno);
            $findCommand->setField('Streetname', $Streetname);
            $findCommand->setField('City', $City);
            $findCommand->setField('State', $State);
            $findCommand->setField('Postalcode', $Postalcode);
            $result = $findCommand->commit();
            $newresponse = $response->withStatus(200);
            return $newresponse->withJson(['success'=>true]);
    }






    public function addjob($request,$response,$args){
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
        $dbobj = new dbconnection\dbconnection();   
        $conn = $dbobj->connect();
        // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $vars = json_decode($request->getBody());
        $count = 0;    
        $id = $args['id'];
        $tagarray = $request->getParsedBody()['array'];
        $jobdata = array( 
            "__kf_UserId" => $id,
            "Company" => $request->getParsedBody()['Company'],
            "DriveDate" => $request->getParsedBody()['DriveDate'],
            "Drivedetails" =>$request->getParsedBody()['Drivedetails'],
            "CompanyWebsite" => $request->getParsedBody()['CompanyWebsite'],
            "CompanyLocation" => $request->getParsedBody()['CompanyLocation']
        );
        $stmt = $conn->createRecord('Job', $jobdata);
        $register = $stmt->commit();
        if (\FileMaker::isError($register)) {
            $findError = 'Find Error: '. $register->getMessage(). ' (' . $register->getCode(). ')';
            $newresponse = $response->withStatus(404);
            return $newresponse->withJson(['success'=>false, "message"=>$findError]);
            
        }
        // $findCommand = $conn->newFindCommand('Job');
        // $findCommand->addFindCriterion('Company', $request->getParsedBody()['Company']);
        // $findCommand->addFindCriterion('DriveDate', $request->getParsedBody()['DriveDate']);
        // $findCommand->addFindCriterion('Drivedetails', $request->getParsedBody()['Drivedetails']);
        // $findCommand->addFindCriterion('CompanyWebsite', $request->getParsedBody()['CompanyWebsite']);
        // $result=$findCommand->execute();
        // $id=$result->getRecords()[0]->_impl->_fields['___kp_JobId'][0];
        // $address = array(
        //     "__kf_JobId" => "$id",
        //     "Doorno" => $request->getParsedBody()['Doorno'],
        //     "Streetname" => $request->getParsedBody()['Streetname'],
        //     "City" =>$request->getParsedBody()['City'],
        //     "State" =>$request->getParsedBody()['State'],
        //     "Postalcode" =>$request->getParsedBody()['Postalcode']
        // );
        // $stmt = $conn->createRecord('Address', $address);
        // $add = $stmt->commit();
        // if(FileMaker::isError($add)) {
        //     $findError = 'Find Error: '. $add->getMessage(). ' (' . $add->getCode(). ')';
        //     $newresponse = $response->withStatus(404);
    
        //     return $newresponse->withJson(['success'=>false, "message"=>$findError]);
            
        // }
     
        else{
            $findCommand = $conn->newFindCommand('Job');
            $findCommand->addFindCriterion('Company', $request->getParsedBody()['Company']);
            $findCommand->addFindCriterion('DriveDate', $request->getParsedBody()['DriveDate']);
            $findCommand->addFindCriterion('Drivedetails', $request->getParsedBody()['Drivedetails']);
            $findCommand->addFindCriterion('CompanyWebsite', $request->getParsedBody()['CompanyWebsite']);
            $result=$findCommand->execute();
            $id=$result->getRecords()[0]->_impl->_fields['___kp_JobId'][0];
            $tagcount=sizeof($tagarray);
            for($i=0;$i<$tagcount;$i++){
                $tag = array(
                    "__kf_Id"=>$id,
                    "TagName" =>$tagarray[$i],
                    "Type" =>"job"
                );
                 $stmt = $conn->createRecord('Tags', $tag);
                 $add = $stmt->commit();
                if(\FileMaker::isError($add)) {
                    $findError = 'Find Error: '. $add->getMessage(). ' (' . $add->getCode(). ')';
                    $newresponse = $response->withStatus(404);
    
                return $newresponse->withJson(['success'=>false, "message"=>$findError]);
            
            }
            }
        
        $findCommand = $conn->newFindCommand('Media');
        $findCommand->addFindCriterion('__kf_Id',$id);
        $findCommand->addFindCriterion('Type','Companylogo');
        $result = $findCommand->execute();
        if (!\FileMaker::isError($result)) {
            $deletecommand=$result->getRecords()[0];
        $deletecommand->delete();
            }
        $userdata = array( 
            "__kf_Id"=>$id,
            "Type"=>"Companylogo",
            // "url"=>$locationpath,
            // "Field"=>$request->getParsedBody()['encoded']
        );
    
        $stmt = $conn->createRecord('Media', $userdata);
        $register = $stmt->commit();
        $findCommand = $conn->newFindCommand('Media');
        $findCommand->addFindCriterion('__kf_Id',$id);
        $findCommand->addFindCriterion('Type','Companylogo');
        $findCommand->setScript('mediaupload',$request->getParsedBody()['image']); 
        $result = $findCommand->execute(); 
            if (\FileMaker::isError($result)) {
                if ($result->code = 401) {
                $findError = 'There are no Records that match that request: '. ' (' .
                $result->getCode() . ')';
                } else {
                $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode()
                . ')';
                }
                $newresponse = $response->withStatus(404);
                return $newresponse->withJson(['success'=>false,'data'=>$findError]);
                }
            $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, "message"=>"added successfully", "data"=>$request->getParsedBody()['image']]);
        }
        
    }
    
    

    public function appliedjobs($request,$response,$args){
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
    $status = $args['status'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindCommand('Application');
    $findCommand->addFindCriterion('__kf_UserId', $id);
    $findCommand->addFindCriterion('Status',$status);
    $result=$findCommand->execute();
    if (\FileMaker::isError($result)) {
        if ($result->code = 401) {
        $findError = 'There are no Records that match that request: '. ' (' .
        $result->code . ')';
        } else {
        $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getcode()
        . ')';
        }
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false ,"message"=>$findError]);
        }
        $count=count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedapplicationdata[$i]=$result->getRecords()[$i]->_impl->_fields['__kf_JobId'][0];
        }
        for($i=0;$i<$count;$i++)
        {
            $findCommand = $fm->newFindCommand('Job');
            $findCommand->addFindCriterion('___kp_JobId',$fetchedapplicationdata[$i]);
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
            $fetchedjobdata[$i]=$result->getRecords()[0]->_impl->_fields;
        //     $__kf_JobId=$result->getRecords()[0]->_impl->_fields['___kp_JobId'][0];
        //     $findCommand = $fm->newFindCommand('Address');
        //     $findCommand->addFindCriterion('__kf_JobId', $__kf_JobId);
        //     $result=$findCommand->execute();
        //     if (FileMaker::isError($result)) {
        //     if ($result->code = 401) {
        //     $findError = 'There are no Records that match that request: '. ' (' .
        //     $result->code . ')';
        //     } else {
        //     $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->code
        //     . ')';
        //     }
        //     $newresponse =  $response->withStatus(404);
        //     return $newresponse->withJson(["success"=>false ,"message"=>$findError]);
        // }   
        // $fetcheduseraddress[$i]=$result->getRecords()[0]->_impl->_fields;
        $fetchedjobcompletedata[$i]=$fetchedjobdata[$i];//fetchuseraddress must be added later
        }
        if($count>=1){
        $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, 'data'=>$fetchedjobcompletedata,'count'=>$count]);
    } else {
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false]);
    }  
    }


    public function retrieveapplication($request,$response,$args){
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
        $dbobj = new dbconnection\dbconnection();
        $fm = $dbobj->connect();
        $findCommand = $fm->newFindCommand('Application');
        $findCommand->addFindCriterion('__kf_UserId', $id);
        $findCommand->addFindCriterion('__kf_JobId',$jobid);
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


    public function viewmatchingjob($request,$response,$args){
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
$findCommand = $fm->newFindAllCommand('Job');
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
    return $newresponse->withJson(["success"=>false,"message"=>$findError]);
    }   
    $count = count($result->getRecords());
    
    $fetchedjobdata[0] = 'There are no matched jobs';
    $j=0;
    for($i=0;$i<$count;$i++){
        $jobid = $result->getRecords()[$i]->_impl->_fields['___kp_JobId'][0];
        $findCommand = $fm->newFindCommand('Application');
        $findCommand->addFindCriterion('__kf_JobId',$jobid);
        $findCommand->addFindCriterion('__kf_UserId', $Id);
        $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
        $check=$findCommand->execute(); 
        if (\FileMaker::isError($check)) {
            if ($result->code = 401) {
            $findError = 'There are no Records that match that request: '. ' (' .
            $result->code . ')';
            $fetchedjobdata[$j]=$result->getRecords()[$i]->_impl->_fields;
            $j++;
            
            
            } else {
            break;
            }
        }   
        continue;   
    }
    $newresponse = $response->withStatus(200);
return $newresponse->withJson(["success"=>true, "data"=>$fetchedjobdata]);
    }



    public function applyjob($request,$response,$args){
        $Id = $args['id'];
        $jobid = $args['jobid'];
        $dbobj = new dbconnection\dbconnection();
        $fm = $dbobj->connect();
        $userdata = array( 
            "__kf_JobId"=>$jobid,
            "__kf_UserId"=>$Id,
            "StudentName"=>$request->getParsedBody()['Firstname']
        );
        $stmt = $fm->createRecord('Application', $userdata);
        $apply = $stmt->commit();
        // $newPerformScript = $fm->newPerformScriptCommand('Application','Get name of student in application');
        // $result = $newPerformScript->execute();
        if (\FileMaker::isError($apply)) {
            $findError = 'Find Error: '. $apply->getMessage(). ' (' . $apply->getCode(). ')';
            $newresponse = $response->withStatus(404);
            return $newresponse->withJson(['success'=>false, "message"=>$findError]);        
        }
        else{
            $newresponse = $response->withStatus(200);
            return $newresponse->withJson(['success'=>true, "message"=>"Applied"]);
        }
    }



    public function addskill($request,$response,$args){
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
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $Id = $args['id'];
    $skill = array(
        "__kf_Id" =>$Id,
        "TagName" =>$request->getParsedBody()['skill'],
        "Type" => 'student'
    );
    $stmt = $fm->createRecord('Tags', $skill);
    $addskill = $stmt->commit();
    if(\FileMaker::isError($addskill)) {
        $findError = 'Find Error: '. $addskill->getMessage(). ' (' . $addskill->getCode(). ')';
        $newresponse = $response->withStatus(404);
        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
    }
        else{       
            $newresponse = $response->withStatus(200);
            return $newresponse->withJson(['success'=>true, "message"=>"added successfully", "data"=>$request->getParsedBody()['skill']]);
        }
    }

    public function deleteskill($request,$response,$args){
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
    $id = $args['id'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $skill = $args['skill'];
    $findCommand = $fm->newFindCommand('Tags');
    $findCommand->addFindCriterion('TagName',$skill);
    $findCommand->addFindCriterion('__kf_Id',$id);
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
        return $newresponse->withJson(["success"=>false,"data"=>$skill]);
    }
    }


    public function viewmatchingjobs($request,$response,$args){
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
    $findCommand = $fm->newFindCommand('Tags');
    $findCommand->addFindCriterion('__kf_Id',$Id);
    $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
    $findCommand->addFindCriterion('Type','student');
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
        return $newresponse->withJson(["success"=>false,"message"=>$findError]);
        }   
        $count = count($result->getRecords());
        for($i=0;$i<$count;$i++){
            $fetchedtags[$i]=$result->getRecords()[$i]->_impl->_fields['TagName'][0];
        }

        $fetchedjobdata[0] = 'There are no matched jobs';
        $j=0;
        for($i=0;$i<sizeof($fetchedtags);$i++){ 
            $tagname = $fetchedtags[$i];
            // print_r($tagname);
            $findCommand = $fm->newFindCommand('Tags');
            $findCommand->addFindCriterion('TagName',$tagname);
            $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
            $findCommand->addFindCriterion('Type','job');
            $check=$findCommand->execute(); 
            if (\FileMaker::isError($check)) {
                        if ($result->code = 401) {
                        $findError = 'There are no Records that match that request: '. ' (' .
                        $result->code . ')';                
                        }       
                    }   
             else {
                for($k=0;$k<count($check->getRecords());$k++){
                $fetchedjobids[$j]=$check->getRecords()[$k]->_impl->_fields['__kf_Id'][0];
                        $j++;
                        }
                        continue;
                    }   
        }
        $fetchedjobids = array_values(array_unique($fetchedjobids));
        $fetchedjobcompletedata = array();
        $j=0;
        for($i=0;$i<sizeof($fetchedjobids);$i++){
            $jobid = $fetchedjobids[$i];
            $findCommand = $fm->newFindCommand('Application');
            $findCommand->addFindCriterion('__kf_JobId',$jobid);
            $findCommand->addFindCriterion('__kf_UserId', $Id);
            $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
            $check=$findCommand->execute(); 
            if (\FileMaker::isError($check)) {
                if ($check->code = 401) {
                    $findError = 'There are no Records that match that request: '. ' (' .
                    $check->code . ')';
                    $getjob = $fm->newFindCommand('Job');
                    $getjob->addFindCriterion('___kp_JobId',$jobid);
                    $getjobresult= $getjob->execute();
                    $fetchedjobdata[$j]=$getjobresult->getRecords()[0]->_impl->_fields;
                    $records[$j]=$getjobresult->getRecords()[0]->_impl;
                    $src[$j]=$getjobresult->getRecords()[0]->_impl->_fields['Media 2::File'];
                    $container_field_array = explode ( '?',$src[$j][0]);
                    $filename = $container_field_array[0];
                    $filename = str_replace ( '/fmi/xml/cnt/', '', $filename );
                    $filename = urldecode ( $filename );
                    $filename_parts = explode( '.', $filename );
                    $extension = $filename_parts[1];
                    $image_mime_types = array( 'gif', 'jpeg', 'jpg', 'png' );
                    if ( in_array ( $extension, $image_mime_types ) ) {                
                        $container_content = $fm -> getContainerData ( $records[$j] -> getField ( 'Media 2::File' ) );
                       $url[$j] = 'data:image/' . $extension . ';base64,' . base64_encode ( $container_content )  ;
                       $fetchedjobdata[$j]['Media 2::File'] = $url[$j];  
                       array_push($fetchedjobcompletedata,array(
                           '___kp_JobId' => $fetchedjobdata[$j]['___kp_JobId'],
                           'Company' => $fetchedjobdata[$j]['Company'],
                           'DriveDate' => $fetchedjobdata[$j]['DriveDate'],
                           'Drivedetails' => $fetchedjobdata[$j]['Drivedetails'],
                           '__kf_UserId' => $fetchedjobdata[$j]['__kf_UserId'],
                           'CompanyWebsite' => $fetchedjobdata[$j]['CompanyWebsite'],
                           'CompanyLocation' => $fetchedjobdata[$j]['CompanyLocation'],
                           'image' => [$fetchedjobdata[$j]['Media 2::File']]
                       ));
                    //   $fetchedjobcompletedata = array_merge($fetchedjobdata , $url); a
                    //    echo '<img src="data:image/' . $extension . ';base64,' . base64_encode ( $container_content ) . '" id="image-' . str_replace ( " ", "-",'File') . '" class="image-container">';
                    } else 
                    {
                        echo 'Mime type "' . $extension . '" is not yet supported.';
                    }
                    $j++;
                           
                            } else {
                            break;
                            }
                        }   
                        continue;   
            }
            // $fetchedjobdata[0]['`Media` 2::File'] = $url;  
            // echo print_r($fetchedjobdata[0]);
        $newresponse = $response->withStatus(200);
    return $newresponse->withJson(["success"=>true, "data"=>$fetchedjobcompletedata]);
    }


    public function relatedskill($request,$response,$args){
        $dbobj = new dbconnection\dbconnection();
        $fm = $dbobj->connect();
        $Id = $args['id'];
        $findCommand = $fm->newFindCommand('Tags');
        $findCommand->addFindCriterion('__kf_Id',$Id);
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
                $fetchedtags[$i]=$result->getRecords()[$i]->_impl->_fields['TagName'][0];
            }            
            if($count>=1){
                $newresponse = $response->withStatus(200);
                //print_r($ph);
                return $newresponse->withJson(['success'=>true, 'data'=>$fetchedtags,'count'=>$count]);
            } 
    }


    public function rejectapplication($request,$response,$args){
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
$findCommand = $fm->newFindCommand('Application');
$findCommand->addFindCriterion('__kf_UserId',$request->getParsedBody()['StudentId']);
$findCommand->addFindCriterion('__kf_JobId',$request->getParsedBody()['JobId']);
$result = $findCommand->execute();
$applicationid = $result->getRecords()[0]->_impl->_fields['___kp_ApplicationId'][0];
$findCommand = $result->getRecords()[0];

$findCommand->setField('Status', $request->getParsedBody()['Status']);
$result = $findCommand->commit();
if (\FileMaker::isError($result)) {
    if ($result->code = 401) {
    $findError = 'There are no Records that match that request: '. ' (' .
    $result->code . ')';
    } else {
    $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->getCode()
    . ')';
    }
    $newresponse =  $response->withStatus(404);
    return $newresponse->withJson(["success"=>false]);
    } 
else{
    

        // $findCommand = $fm->newFindCommand('Application');
        // $findCommand->addFindCriterion('___kp_ApplicationId',$applicationid);
        // $findCommand->setScript('rejectapplication',$applicationid); 
        // $result = $findCommand->execute(); 
        // if (\FileMaker::isError($result)) {
        //     if ($result->code = 401) {
        //     $findError = 'There are no Records that match that request: '. ' (' .
        //     $result->code . ')';
        //     } else {
        //     $findError = 'Find Error: '. $result->getMessage(). ' (' . $result->code
        //     . ')';
        //     }
        //     $newresponse =  $response->withStatus(404);
        //     return $newresponse->withJson(["success"=>$findError]);
        //     }   
    $newresponse = $response->withStatus(200);
return $newresponse->withJson(['success'=>true,'message'=>'updated successfully']);
}

    }



    public function filterjobs($request,$response,$args){
        $Id = $args['id'];
        $dbobj = new dbconnection\dbconnection();
        $fm = $dbobj->connect();
        $skillfilter = $request->getParsedBody()['skillfilter'];
        $skillfiltercount = count($skillfilter);
        $fetchedjobids[0] = 'There are no matched jobs';
        $j=0;
        for($i=0;$i<$skillfiltercount;$i++){
           $tagname = $skillfilter[$i];
           $findCommand = $fm->newFindCommand('Tags');
           $findCommand->addFindCriterion('TagName',$tagname);
           $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
           $findCommand->addFindCriterion('Type','job');
           $result=$findCommand->execute(); 
           if (\FileMaker::isError($result)) {
                       if ($result->code = 401) {
                       $findError = 'There are no Records that match that request: '. ' (' .
                       $result->code . ')';                
                       }       
                   }   
            else {
               for($k=0;$k<count($result->getRecords());$k++){
               $fetchedjobids[$j]=$result->getRecords()[$k]->_impl->_fields['__kf_Id'][0];
                       $j++;
                       }
                       continue;
                   }   
        }
        $fetchedjobids1;
        if(count($request->getParsedBody()['companyfilter'])!=0){
            $companyfilter = $request->getParsedBody()['companyfilter'];
            $j=0;
            for($i=0;$i<count($request->getParsedBody()['companyfilter']);$i++){
               $companyname = $companyfilter[$i];
               $findCommand = $fm->newFindCommand('Job');
               $findCommand->addFindCriterion('Company',$companyname);
               $result=$findCommand->execute(); 
               if (\FileMaker::isError($result)) {
                           if ($result->code = 401) {
                           $findError = 'There are no Records that match that request: '. ' (' .
                           $result->code . ')';                
                           }       
                       }   
                else {
                   for($k=0;$k<count($result->getRecords());$k++){
                   $fetchedjobids1[$j]=$result->getRecords()[$k]->_impl->_fields['___kp_JobId'][0];
                           $j++;
                           }
                           continue;
                       }   
            }
            if($fetchedjobids[0] !='There are no matched jobs'){
                $fetchedjobids = array_merge($fetchedjobids1,$fetchedjobids);
            }
            else{
                $fetchedjobids = $fetchedjobids1;
            }
        }
        $fetchedjobdata[0] = 'There are no matched jobs';
        if($fetchedjobids[0] != 'There are no matched jobs'){
            $fetchedjobids = array_values(array_unique($fetchedjobids));
            $j=0;
            for($i=0;$i<sizeof($fetchedjobids);$i++){
                $jobid = $fetchedjobids[$i];
                $findCommand = $fm->newFindCommand('Application');
                $findCommand->addFindCriterion('__kf_JobId',$jobid);
                $findCommand->addFindCriterion('__kf_UserId', $Id);
                $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
                $check=$findCommand->execute(); 
                if (\FileMaker::isError($check)) {
                    if ($check->code = 401) {
                        $findError = 'There are no Records that match that request: '. ' (' .
                        $check->code . ')';
                        $getjob = $fm->newFindCommand('Job');
                        $getjob->addFindCriterion('___kp_JobId',$jobid);
                        $getjobresult= $getjob->execute();
                        $fetchedjobdata[$j]=$getjobresult->getRecords()[0]->_impl->_fields;
                                $j++;
                                } else {
                                break;
                                }
                            }   
                            continue;   
                }
            }
            $newresponse = $response->withStatus(200);
        return $newresponse->withJson(["success"=>true, "data"=>$fetchedjobdata]);
    }
}