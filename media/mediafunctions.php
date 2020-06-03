<?php


namespace media;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT ; 

use config as dbconnection;


class media{
   public function uploadresume($request,$response,$args){
         // $temppath = $_FILES['file']['tmp_name'];
    // $actualName = $_FILES['file']['name'];
    // $actualpath = dirname(__FILE__)."\\temp\\".$actualName;
    // move_uploaded_file($temppath,$actualpath);
    // $locationpath = "http://localhost/crs/public/temp/".$actualName;

    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $Id = $args['id'];
    $findCommand = $fm->newFindCommand('Media');
    $findCommand->addFindCriterion('__kf_Id',$Id);
    $findCommand->addFindCriterion('Type','Student');
    $result = $findCommand->execute();
    if (!\FileMaker::isError($result)) {
        $deletecommand=$result->getRecords()[0];
    $deletecommand->delete();
        }
    $userdata = array( 
        "__kf_Id"=>$Id,
        "Type"=>"Student",
        // "url"=>$locationpath,
        // "Field"=>$request->getParsedBody()['encoded']
    );

    $stmt = $fm->createRecord('Media', $userdata);
    $register = $stmt->commit();
    $findCommand = $fm->newFindCommand('Media');
    $findCommand->addFindCriterion('__kf_Id',$Id);
    $findCommand->addFindCriterion('Type','Student');
    $findCommand->setScript('mediaupload',$request->getParsedBody()['encoded']); 
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
            // unlink($actualpath);
        $newresponse = $response->withStatus(200);
    return $newresponse->withJson(['success'=>true,'data'=>$request->getParsedBody()['encoded']]);
    }
}