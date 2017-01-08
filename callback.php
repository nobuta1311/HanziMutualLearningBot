<?php
require_once './lineapikey.php';
require_once __DIR__ . '/../line/vendor/autoload.php';
require_once "./command_carousel.php";
require_once "./ModUserAttr.php";
require_once "./UserControl.php";
//require_once "./HanziPronunciation/hanzi_pinyin.php";
//require_once "./ImageCognition/HanziCognition.php";
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder as MultiMessageBuilder;
//POST
$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($lineaccesstoken);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $linechannelsecret]);
$MessageBuilder = null;
$profile = getProfile($event);

//イベントタイプ判別
if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
    $MessageBuilder = new MultiMessageBuilder();	//メッセージ用意    
    addUser($profile["id"]);

    //テキストメッセージにはオウムで返す
    if ("text" == $event->message->type) {
	$received = $event->message->text;
	$received = str_replace(array("\r\n", "\r", "\n"), '', $received);
	exec("./HanziPronunciation/hanzi_pinyin.php ".urlencode($received)." 0 ".getInfo($profile["id"],"lang"),$result);
	
	$MessageBuilder_part =  new TextMessageBuilder($result[0]);
	$MessageBuilder->add($MessageBuilder_part);
    }elseif("image" == $event->message->type){ 
	$response = $bot->getMessageContent($event->message->id);
        if ($response->isSucceeded()) {
           	$tempfile = "./ImageCognition/images/".$event->message->id;
    		file_put_contents($tempfile, $response->getRawBody());
		exec("./ImageCognition/hanzi_cognition.php .".$tempfile." 0 ".getInfo($profile["id"],"lang"),$result);
		$received = implode(str_replace(array(";","\r\n", "\r", "\n"), '', $result));
		file_put_contents($tempfile.".txt",$received);
		//syslog(LOG_EMERG,print_r($received,true));
		exec("./HanziPronunciation/hanzi_pinyin.php ".urlencode($received)." 1",$result_2);
		//syslog(LOG_EMERG,print_r($result_2,true));
		$MessageBuilder_part =  new TextMessageBuilder($result_2[0]);
		$MessageBuilder->add($MessageBuilder_part);
	
	} else {
    	//	syslog(LOG_EMERG,$response->getHTTPStatus() . ' ' . $response->getRawBody(),true);
		$MessageBuilder_part = new TextMessageBuilder("画像アップロード失敗");
	}
    }elseif("video" == $event->message->type){
    }elseif("audio" == $event->message->type){
    }elseif("sticker" == $event->message->type){
	$MessageBuilder_part = command_carousel();
	$MessageBuilder->add($MessageBuilder_part);

    }elseif("location" == $event->message->type){//latitude longitude
	$lat=$event->message->latitude;
	$lon=$event->message->longitude;
	if(mb_strstr($event->message->address,",")){//カンマを含む場合は英語表記
	exec("./GetAddress/get_address.php ".$lat." ".$lon,$location);
	}else{
	$location[0]=$event->message->address;
	}
	if($location[0]!=""){
	exec("./HanziPronunciation/hanzi_pinyin.php ".urlencode($location[0])." 0 ".getInfo($profile["id"],"lang"),$result);
	$MessageBuilder_part = new TextMessageBuilder($result[0]);
	$MessageBuilder->add($MessageBuilder_part);
	}
    }else {
	$MessageBuilder_part = command_carousel();
//	syslog(LOG_EMERG, print_r($MessageBuilder_part, true));

	$MessageBuilder->add($MessageBuilder_part);
    }
} elseif("postback" == $event->type){
	$postbackeddata=explode("?",$event->postback->data);
	$MessageBuilder = new MultiMessageBuilder();	//メッセージ用意    

	if($postbackeddata[0]=="ALTINFO"){
		$OutPutModes=["注音モードに変更しました","拼音モードに変更しました"]; 
		$OutPutMode=explode("=",$postbackeddata[1])[1];
		altInfo($profile["id"],"lang",$OutPutMode);
		$MessageBuilder_part =  new TextMessageBuilder($OutPutModes[$OutPutMode]);
		$MessageBuilder->add($MessageBuilder_part);

	}
	
} elseif ("follow" == $event->type) {        //お友達追加時    
    $MessageBuilder = new MultiMessageBuilder();	
    $MessageBuilder_part = new TextMessageBuilder("お友達追加ありがとうございます．このBotは中国語を効率的に学ぶためのLINEbotです．まず，あなたの情報を教えてください．");
    $MessageBuilder->add($MessageBuilder_part);
    $MessageBuilder_part = modUserAttr();
    $MessageBuilder->add($MessageBuilder_part);

    addUser($profile["id"]);

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
