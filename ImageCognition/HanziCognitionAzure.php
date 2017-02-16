<?php
function hanziCognitionAzure($filename,$userinfo){
//require_once("./ImageCognition/apikey_azure.php");
require_once 'HTTP/Request2.php';
include("./ImageCognition/apikey_azure.php");
$request = new Http_Request2('https://westus.api.cognitive.microsoft.com/vision/v1.0/ocr');
$url = $request->getUrl();

$headers = array(
    // Request headers
    'Content-Type' => 'application/json',
    'Ocp-Apim-Subscription-Key' => $azure_vision_token,
);
$request->setHeader($headers);

$parameters = array(
    // Request parameters
    'language' => ($userinfo%2==0 ? 'zh-Hans' : 'zh-Hant'),
    'detectOrientation ' => 'true',
);

$url->setQueryVariables($parameters);

$request->setMethod(HTTP_Request2::METHOD_POST);

// Request body
$request->setBody("{\"url\":\"".$httppath.$filename.".png\"}");

try
{
    $response = $request->send();
    $result= json_decode($response->getBody(),true);
}catch (HttpException $ex)
{
    echo $ex;
}
$returnar=[];
file_put_contents("./OUTPUTLOG",json_encode($result));
for($h=0;$h<sizeof($result["regions"]);$h++){
for($i=0;$i<sizeof($result["regions"][$h]["lines"]);$i++){
for($j=0;$j<sizeof($result["regions"][$h]["lines"][$i]["words"]);$j++){
	if(isset($result["regions"][$h]["lines"][$i]["words"][$j]["text"]))
		$returnar[]=$result["regions"][$h]["lines"][$i]["words"][$j]["text"];
	}
}$returnar[]="\n";
}
//syslog(LOG_EMERG,print_r($returnar,true));
return $returnar;
}
?>


