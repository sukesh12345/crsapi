<?php
header ("Access-Control-Allow-Origin:*");
header ("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header ("Access-Control-Allow-Headers: origin, x-requested-with, content-type, authorization");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT ; 

use config as dbconnection;

require __DIR__ . '/../vendor/autoload.php';
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);               
$app = new \Slim\App($c);
//register 
$app->post('/register', function(Request $request, Response $response)
{
    $dbobj = new dbconnection\dbconnection();   
    $conn = $dbobj->connect();
    // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    $vars = json_decode($request->getBody());
    $count = 0;            
    foreach($vars as $key) {
        $count++;
    }
    if( $count<13) {
        $newresponse = $response->withStatus(400);
        return $newresponse->withJson(["message"=>"request body is not appropriate","count"=>$count]);
    }
    $userdata = array( 
        "Type" =>$request->getParsedBody()['type'],
        "Firstname" =>$request->getParsedBody()['firstname'],
        "Lastname" =>$request->getParsedBody()['lastname'],
        "Gender" => $request->getParsedBody()['gender'],
        "Dob" =>$request->getParsedBody()['date'],
        "Email" => $request->getParsedBody()['email'],
        "Telephone" => $request->getParsedBody()['telephone'],
        "Password" =>$request->getParsedBody()['password'],
        "Confirmpassword" =>$request->getParsedBody()['confirmpassword'],
        "Type"=>$request->getParsedBody()['type']
    );
    $stmt = $conn->createRecord('User', $userdata);
    $register = $stmt->commit();
    if (FileMaker::isError($register)) {
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
    $findCommand = $conn->newFindCommand('User');
    $findCommand->addFindCriterion('Telephone', $request->getParsedBody()['telephone']);
    $result=$findCommand->execute();
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
    if(FileMaker::isError($add)) {
        $findError = 'Find Error: '. $add->getMessage(). ' (' . $add->getCode(). ')';
        $newresponse = $response->withStatus(404);

        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
        
    }
 
        else{
            
            $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, "message"=>"registered successfully", "data"=>$request->getParsedBody()['telephone']]);
        }
    
});
//login
$app->post('/api/users', function(Request $request, Response $response)
{
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $jwt = new config\jwt();
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
    $password = $request->getParsedBody()['password'];
    $findCommand = $fm->newFindCommand('User');
    $findCommand->addFindCriterion('Telephone',$username);
    $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
    $findCommand->addFindCriterion('Password', $password);
    $result=$findCommand->execute();   
    if (FileMaker::isError($result)) {
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
        $token = $jwt->jwttokenencryption( $id);
    return $response->withJson(["status"=>true,"data"=>$id , "token"=>$token]);
    } else {
        $newresponse = $response->withStatus(401);
        return $newresponse->withJson(["status"=>false, "message"=>"credentials  dosent match each other"]);
    }
});
        
// view a record
$app->get('/api/users/{id}', function(Request $request, Response $response, array $args)
{ 

    $jwt = new config\jwt();
            
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
    if (FileMaker::isError($result)) {
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
    if (FileMaker::isError($result)) {
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
});

//get jobs posted by the recruiter
$app->get('/api/users/{id}/jobs', function(Request $request, Response $response, array $args)
        {
            $jwt = new config\jwt();
            
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
            if (FileMaker::isError($result)) {
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
        });

//Update
$app->put('/api/users/{id}', function(Request $request, Response $response,array $args) 
        {  
            $jwt = new config\jwt();
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
});

//view job  applications
$app->get('/api/users/{id}/jobs/applications/{jobid}', function(Request $request, Response $response, array $args){
    $jwt = new config\jwt();
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
    $findCommand->addFindCriterion('__kf_JobId', $jobid);
    $result=$findCommand->execute();
    if (FileMaker::isError($result)) {
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
            if (FileMaker::isError($result)) {
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
            if (FileMaker::isError($result)) {
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
});

//post a job
$app->post('/api/users/{id}/addjob', function(Request $request, Response $response, array $args)
{   
    $jwt = new config\jwt();
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
        "CompanyWebsite" => $request->getParsedBody()['CompanyWebsite']
    );
    $stmt = $conn->createRecord('Job', $jobdata);
    $register = $stmt->commit();
    if (FileMaker::isError($register)) {
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
            if(FileMaker::isError($add)) {
                $findError = 'Find Error: '. $add->getMessage(). ' (' . $add->getCode(). ')';
                $newresponse = $response->withStatus(404);

            return $newresponse->withJson(['success'=>false, "message"=>$findError]);
        
        }
        }
        $newresponse = $response->withStatus(200);
    return $newresponse->withJson(['success'=>true, "message"=>"added successfully", "data"=>$request->getParsedBody()['Company']]);
    }
    
});


//view applied jobs
$app->get('/api/users/{id}/appliedjobs', function(Request $request, Response $response, array $args){
    $jwt = new config\jwt();
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
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindCommand('Application');
    $findCommand->addFindCriterion('__kf_UserId', $id);
    $result=$findCommand->execute();
    if (FileMaker::isError($result)) {
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
            if (FileMaker::isError($result)) {
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
});

//retieve job application
$app->delete('/api/users/{id}/appliedjobs/{jobid}', function(Request $request, Response $response, array $args){
    $jwt = new config\jwt();
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
    if (FileMaker::isError($result)) {
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
});

//retrieve posted job
$app->delete('/api/users/{jobid}', function(Request $request, Response $response, array $args){
    $jwt = new config\jwt();
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
    if (FileMaker::isError($result)) {
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
});

//view matching jobs
$app->get('/api/users/{id}/matchingjobs/', function(Request $request, Response $response, array $args)
{ 
    $jwt = new config\jwt();
            
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
    if (FileMaker::isError($result)) {
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
            if (FileMaker::isError($check)) {
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
});

//apply job
$app->post('/api/users/{id}/matchingjobs/applyjob/{jobid}', function(Request $request, Response $response, array $args)
{ 
    $Id = $args['id'];
    $jobid = $args['jobid'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $userdata = array( 
        "__kf_JobId"=>$jobid,
        "__kf_UserId"=>$Id
    );
    $stmt = $fm->createRecord('Application', $userdata);
    $apply = $stmt->commit();
    if (FileMaker::isError($apply)) {
        $findError = 'Find Error: '. $apply->getMessage(). ' (' . $apply->getCode(). ')';
        $newresponse = $response->withStatus(404);
        return $newresponse->withJson(['success'=>false, "message"=>$findError]);        
    }
    else{
        $newresponse = $response->withStatus(200);
        return $newresponse->withJson(['success'=>true, "message"=>"Applied"]);
    }
});

//stateslist
$app->get('/statesname',function(Request $request, Response $response, array $args){
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindAllCommand('states');
    $result=$findCommand->execute(); 
    if (FileMaker::isError($result)) {
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
});

//skillslist
$app->get('/allskills',function(Request $request, Response $response, array $args){
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindAllCommand('TagMaster');
    $result=$findCommand->execute(); 
    if (FileMaker::isError($result)) {
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
});


//add skill
$app->post('/api/users/{id}/addskill',function(Request $request,Response $response,array $args){
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
    if(FileMaker::isError($addskill)) {
        $findError = 'Find Error: '. $addskill->getMessage(). ' (' . $addskill->getCode(). ')';
        $newresponse = $response->withStatus(404);
        return $newresponse->withJson(['success'=>false, "message"=>$findError]);
    }
        else{       
            $newresponse = $response->withStatus(200);
            return $newresponse->withJson(['success'=>true, "message"=>"added successfully", "data"=>$request->getParsedBody()['skill']]);
        }
});

//delete skill
$app->delete('/api/users/{id}/removeskill{skill}',function(Request $request,Response $response,array $args){
    $id = $args['id'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $skill = $args['skill'];
    $findCommand = $fm->newFindCommand('Tags');
    $findCommand->addFindCriterion('TagName',$skill);
    $findCommand->addFindCriterion('__kf_Id',$id);
    $result=$findCommand->execute(); 
    if (FileMaker::isError($result)) {
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
});



//get realted skills
$app->get('/api/users/{id}/skills',function(Request $request,Response $response,array $args){
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $Id = $args['id'];
    $findCommand = $fm->newFindCommand('Tags');
    $findCommand->addFindCriterion('__kf_Id',$Id);
    $result=$findCommand->execute();
    if (FileMaker::isError($result)) {
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
});

//viewmatching jobs
$app->post('/api/users/{id}/matchingjobs/', function(Request $request, Response $response, array $args)
{ 
    // $jwt = new config\jwt();
            
    //         if( $request->hasHeader("Authorization") == false) {
    //             $newresponse = $response->withStatus(400);
    //             return $newresponse->withJson(["message"=>"required jwt token is not recieved"]);
    //         }
    //         $header = $request->getHeader("Authorization");
    //         $vars = $header[0];
    //         $token = json_decode($jwt->jwttokendecryption($vars));
    //         if( $token->verification == "failed") {
    //             // header("location: index.html");
    //             $newresponse = $response->withStatus(401);
    //             return $newresponse->withJson(["message"=>"you are not authorized"]);
    //         }
    $Id = $args['id'];
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $findCommand = $fm->newFindCommand('Tags');
    $findCommand->addFindCriterion('__kf_Id',$Id);
    $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
    $findCommand->addFindCriterion('Type','student');
    $result=$findCommand->execute(); 
    if (FileMaker::isError($result)) {
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
            if (FileMaker::isError($check)) {
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

        $j=0;
        for($i=0;$i<sizeof($fetchedjobids);$i++){
            $jobid = $fetchedjobids[$i];
            $findCommand = $fm->newFindCommand('Application');
            $findCommand->addFindCriterion('__kf_JobId',$jobid);
            $findCommand->addFindCriterion('__kf_UserId', $Id);
            $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
            $check=$findCommand->execute(); 
            if (FileMaker::isError($check)) {
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
            
        // for($i=0;$i<$count;$i++){
        //     $jobid = $fetchedjobids[$i];
        //     $findCommand = $fm->newFindCommand('Application');
        //     $findCommand->addFindCriterion('__kf_JobId',$jobid);
        //     $findCommand->addFindCriterion('__kf_UserId', $Id);
        //     $findCommand->setLogicalOperator(FILEMAKER_FIND_AND);
        //     $check=$findCommand->execute(); 
        //     if (FileMaker::isError($check)) {
        //         if ($check->code = 401) {
        //         $findError = 'There are no Records that match that request: '. ' (' .
        //         $$check->code . ')';
        //         $fetchedjobdata[$j]=$result->getRecords()[$i]->_impl->_fields;
        //         $j++;
                
                
        //         } else {
        //         break;
        //         }
        //     }   
        //     continue;   
        // }
       
        $newresponse = $response->withStatus(200);
    return $newresponse->withJson(["success"=>true, "data"=>$fetchedjobdata]);
});

$app->run();


