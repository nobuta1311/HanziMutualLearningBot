<?php
function japaneseToHiragana($input){
include "appkey.php";
$url = 'https://labs.goo.ne.jp/api/hiragana';
$data = array(
    'app_id' => $appID,
    'sentence' => $input,
    'output_type' => 'hiragana',
);
$headers = array(
    'Content-Type: application/x-www-form-urlencoded',
);
$options = array('http' => array(
    'method' => 'POST',
    'content' => http_build_query($data),
    'header' => implode("\r\n", $headers),
));
$contents = file_get_contents($url, false, stream_context_create($options));
return  ((json_decode($contents,true))["converted"]);
}
?>
