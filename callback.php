<?php
require_once './lineapikey.php';
require_once __DIR__ . '/../line/vendor/autoload.php';
require_once "./command_carousel.php";
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder as MultiMessageBuilder;
//POST
$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($lineaccesstoken);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $linechannelsecret]);
$MessageBuilder = null;

//イベントタイプ判別
if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
    $MessageBuilder = new MultiMessageBuilder();	//メッセージ用意
    //テキストメッセージにはオウムで返す
    if ("text" == $event->message->type) {
	$reserved = $event->message->text;
	exec("php ./HanziPronunciation/hanzi_pinyin.php ".$reserved,$result);
	
	$MessageBuilder_part =  new TextMessageBuilder($result[0]);
	$MessageBuilder->add($MessageBuilder_part);
    } else {
	$MessageBuilder_part = command_carousel();
	syslog(LOG_EMERG, print_r($MessageBuilder_part, true));

	$MessageBuilder->add($MessageBuilder_part);
    }
} elseif("postback" == $event->type){
   
} elseif ("follow" == $event->type) {        //お友達追加時
    $MessageBuilder = new TextMessageBuilder("友達追加ありがとうございます!");
} elseif ("join" == $event->type) {           //グループに入ったときのイベント
    $MessageBuilder= new TextMessageBuilder('グループご招待ありがとうございます．');
} elseif("leave" == $event->type){
						//退出させられた
} elseif("unfollow" == $event->type){
} else {					//ブロックされた
    $MessageBuilder=new TextMessageBuilder("対応出来ません");
}

if($MessageBuilder!=null){
	$response = $bot->replyMessage($event->replyToken, $MessageBuilder);
}
//syslog(LOG_EMERG, print_r($event->replyToken, true));
//syslog(LOG_EMERG, print_r($response, true));
return;
