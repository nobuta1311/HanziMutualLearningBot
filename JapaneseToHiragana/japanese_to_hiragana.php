<?php
include "apekye.php";

$input = $argv[1];

$url = 'https://labs.goo.ne.jp/api/hiragana';
$data = array(
    'app_id' => $appID,
    'sentence' => $input,
    'output_type' => 'hiragana',
);
$headers = array(
    'application: json', //jsonで送信
    'User-Agent: My User Agent 1.0',    //ユーザエージェントの指定
    'Authorization: Basic '.base64_encode('user:pass'),//ベーシック認証
);
$options = array('http' => array(
    'method' => 'POST',
    'content' => http_build_query($data),
    'header' => implode("\r\n", $headers),
));
$contents = file_get_contents($url, false, stream_context_create($options));
?>