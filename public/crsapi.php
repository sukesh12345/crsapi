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
        
//view a record
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
    $ph=$result->getRecords()[0]->_impl->_fields;
    if(count($result->getRecords())==1){
        $newresponse = $response->withStatus(200);
        //print_r($ph);
        return $newresponse->withJson(['success'=>true, 'data'=>$ph]);
    } else {
        $newresponse =  $response->withStatus(404);
        return $newresponse->withJson(["success"=>false]);
    }
});

$app->run();