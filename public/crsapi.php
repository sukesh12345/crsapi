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
    return users\userclass::register($request, $response);   
});
//login
$app->post('/api/users', function(Request $request, Response $response)
{
   return users\userclass::login($request,$response);
});
        
// view a record
$app->get('/api/users/{id}', function(Request $request, Response $response, array $args)
{ 
    return users\userclass::getuser($request,$response,$args);
});

//get jobs posted by the recruiter
$app->get('/api/users/{id}/jobs', function(Request $request, Response $response, array $args)
{
     return jobs\jobsclass::getjobs($request,$response,$args);       
});

//Update
$app->put('/api/users/{id}', function(Request $request, Response $response,array $args) 
        {  
            return users\userclass::updateuser($request,$response,$args);
});

//view job  applications
$app->get('/api/users/{id}/jobs/applications/{jobid}/{status}', function(Request $request, Response $response, array $args){
    return jobs\jobsclass::viewjobapplications($request,$response,$args);
});

//post a job
$app->post('/api/users/{id}/addjob', function(Request $request, Response $response, array $args)
{   
    return users\userclass::addjob($request,$response,$args);
});


//view applied jobs 
$app->get('/api/users/{id}/appliedjobs/{status}', function(Request $request, Response $response, array $args){
    return users\userclass::appliedjobs($request,$response,$args);
});

//retieve job application
$app->delete('/api/users/{id}/appliedjobs/{jobid}', function(Request $request, Response $response, array $args){
   return users\userclass::retrieveapplication($request,$response,$args);
});

//retrieve posted job
$app->delete('/api/users/{jobid}', function(Request $request, Response $response, array $args){
    return jobs\jobsclass::retrievejobpost($request,$response,$args);
});

//view matching jobs
$app->post('/api/users/{id}/matchingjobs/', function(Request $request, Response $response, array $args)
{ 
   return users\userclass::viewmatchingjob($request,$response,$args);
});

//apply job
$app->post('/api/users/{id}/matchingjobs/applyjob/{jobid}', function(Request $request, Response $response, array $args)
{ 
   return users\userclass::applyjob($request,$response,$args);
});

//stateslist
$app->get('/statesname',function(Request $request, Response $response, array $args){
    return valuelists\valueclass::stateslist($request,$response);
});

//skillslist
$app->get('/allskills',function(Request $request, Response $response, array $args){
    return valuelists\valueclass::skilllist($request,$response);
});


//add skill
$app->post('/api/users/{id}/addskill',function(Request $request,Response $response,array $args){
    return users\userclass::addskill($request,$response,$args);
});

//delete skill
$app->delete('/api/users/{id}/removeskill/{skill}',function(Request $request,Response $response,array $args){
    return users\userclass::deleteskill($request,$response,$args);
});



//get related skills
$app->get('/api/users/{id}/skills',function(Request $request,Response $response,array $args){
   return users\userclass::relatedskill($request,$response,$args);
});

//viewmatching jobs
$app->get('/api/users/{id}/matchingjobs/', function(Request $request, Response $response, array $args)
{ 
    return users\userclass::viewmatchingjobs($request,$response,$args);
});


//select or reject an application
$app->patch('/api/users/{id}/applications/updatestatus', function(Request $request, Response $response, array $args)
{ 
   return users\userclass::rejectapplication($request,$response,$args);
});


//get all list of companies for company filters
$app->get('/companies' ,function(Request $request,Response $response,array $args){
    return valuelists\valueclass::companies($request,$response,$args);
});


//Uploading resume 
$app->post('/uploadresume/{id}',function(Request $request,Response $response,array $args){
    return media\media::uploadresume($request, $response,$args);
});

$app->get('/test/{id}',function(Request $request, Response $response, array $args){
    $dbobj = new dbconnection\dbconnection();
    $fm = $dbobj->connect();
    $id = $args['id'];
    $findCommand = $fm->newFindCommand('Media');
    $findCommand->addFindCriterion('__kf_Id',$id);
    // $findCommand->addFindCriterion('Type','Companylogo');
    // $findCommand->setScript('addresume',$locationpath); 
    $result = $findCommand->execute(); 
        if (FileMaker::isError($result)) {
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
            $records = $result->getRecords()[0]->_impl;
            // $container = $records->getField('File');
            $src=$records->getField('File');
            $container_field_array = explode ( '?',$src);
            $filename = $container_field_array[0];
            $filename = str_replace ( '/fmi/xml/cnt/', '', $filename );
            $filename = urldecode ( $filename );
            $filename_parts = explode( '.', $filename );
            $extension = $filename_parts[1];
            $image_mime_types = array( 'gif', 'jpeg', 'jpg', 'png' ,'pdf' );
            if ( in_array ( $extension, $image_mime_types ) ) {                
                $container_content = $fm -> getContainerData ( $records -> getField ( 'File' ) );
               $url = 'data:application/' . $extension . ';base64,' . base64_encode ( $container_content )  ;
            //    echo '<img src="data:image/' . $extension . ';base64,' . base64_encode ( $container_content ) . '" id="image-' . str_replace ( " ", "-",'File') . '" class="image-container">';
            } else 
            {
                echo 'Mime type "' . $extension . '" is not yet supported.';
            }
            // $base64 = base64_encode ( $container_content );
            // $decoded = base64_decode($base64);
            
// $file = 'resume.pdf';
// file_put_contents($file, $decoded);

// if (file_exists($file)) {
//     header('Content-Description: File Transfer');
//     header('Content-Type: application/octet-stream');
//     header('Content-Disposition: attachment; filename="'.basename($file).'"');
//     header('Expires: 0');
//     header('Cache-Control: must-revalidate');
//     header('Pragma: public');
//     header('Content-Length: ' . filesize($file));
//     readfile($file);

/////////////////////////////////////////////////////

// header('Content-Description: File Transfer');
// header('Content-Type: application/pdf');
// header('Content-Disposition: attachment; filename='.$file);
// header('Content-Transfer-Encoding: binary');
// header('Expires: 0');
// header('Cache-Control: must-revalidate');
// header('Pragma: public');
// header('Content-Length: ' . strlen($file));
// ob_clean();
// flush();
//     exit;
// }
        
        // echo $fm->getContainerData($data);
        $newresponse = $response->withStatus(200);
    // //    print_r(json_encode($data,JSON_UNESCAPED_SLASHES));
    return $newresponse->withJson(['success'=>true,'data'=> $url]);
});


//filtered jobs
$app->post('/api/users/{id}/filteredjobs', function(Request $request, Response $response, array $args)
{
   return users\userclass::filterjobs($request,$response,$args);
});



$app->run();


