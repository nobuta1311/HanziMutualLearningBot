<?php
require_once './lineapikey.php';
require_once __DIR__ . '/../line/vendor/autoload.php';
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder as MultiMessageBuilder;
//POST
include "command_carousel.php";
$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($lineaccesstoken);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $linechannelsecret]);

$MessageBuilder = new MultiMessageBuilder();

//イベントタイプ判別
if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
    //テキストメッセージにはオウムで返す
    if ("text" == $event->message->type) {
	$reserved = $event->message->text;
	exec("php ./HanziPronunciation/hanzi_pinyin.php ".$reserved,$result);
	
	$MessageBuilder_part =  new TextMessageBuilder($result[0]);
	$MessageBuilder->add($MessageBuilder_part);
    } else {
	$MessageBuilder = command_carousel();
    }
} elseif ("follow" == $event->type) {        //お友達追加時
    $MessageBuilder = new TextMessageBuilder("よろしくー．");
} elseif ("join" == $event->type) {           //グループに入ったときのイベント
    $MessageBuilder = new TextMessageBuilder('ご招待ありがとうございます．');
} else {
    //なにもしない
}


$response = $bot->replyMessage($event->replyToken, $MessageBuilder);
//syslog(LOG_EMERG, print_r($event->replyToken, true));
//syslog(LOG_EMERG, print_r($response, true));
return;
