#!/usr/bin/php
<?php
include "appkey.php";
if($argc==1){
    print "ERROR";
    return;
}else{
        $input = "";
        for($i=1;$i<$argc;$i++)
                $input.=$argv[$i];
}
$url = 'https://labs.goo.ne.jp/api/hiragana';
$data = array(
    'app_id' => $appID,
    'sentence' => $input,
    'output_type' => 'hiragana',
);
$headers = array(
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: My User Agent 1.0',    //ユーザエージェントの指定
);
$options = array('http' => array(
    'method' => 'POST',
    'content' => http_build_query($data),
    'header' => implode("\r\n", $headers),
));
$contents = file_get_contents($url, false, stream_context_create($options));
print ((json_decode($contents,true))[]"converted"]);
?>