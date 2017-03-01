<?php
require_once './lineapikey.php';
require_once __DIR__ . '/../line/vendor/autoload.php';
require_once "./Command_carousel.php";
require_once "./Others_carousel.php";
require_once "./ModUserAttr.php";
require_once "./UserControl.php";
require_once "./TextData.txt";
require_once "./TransHanzi/TransSimpTrad.php";
require_once "./TransHanzi/TransOtherHanzi.php";
require_once "./HanziPronunciation/HanziPinyin.php";
require_once "./Voice/GenerateVoice.php";
require_once "./ImageCognition/HanziCognitionAzure.php";
require_once "./ImageCognition/HanziCognitionGoogle.php";
require_once "./ImageCognition/OverWrite.php";
require_once "./Log/LoggingInput.php";
require_once "./Learning/SendQuiz.php";
require_once "./Learning/ShowLearnt.php";
require_once "./Log/SendQuery.php";
//require_once "./ImageCognition/HanziCognition.php";
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder as AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder as ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder as MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder	as ConfirmTemplateBuilder; 
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder as MessageTemplateActionBuilder;
$input = file_get_contents('php://input');
$json = json_decode($input);
for($v=0;$v<sizeof($json->events);$v++){
$event = $json->events[$v];//ここは複数対応できるように
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($lineaccesstoken);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $linechannelsecret]);

$profile = getProfile($bot,$event);


$MessageBuilder = new MultiMessageBuilder();	//メッセージ用意    
//イベントタイプ判別
if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
    if ("text" == $event->message->type) {
	$received = $event->message->text;
	$received = str_replace(array("\r\n", "\r", "\n"), '', $received);

	if(mb_substr($received,0,3)=="@@@"){    
		$inputbytxt = explode("?",$received);
		$MessageBuilder=altByPostback($MessageBuilder,mb_substr($inputbytxt[0],3),$inputbytxt[1],$profile);
	}else{
		$MessageBuilder=baseBehavior($MessageBuilder,$received,$profile,"text");
   	}//end of else

    }elseif("image" == $event->message->type){ 
	$response = $bot->getMessageContent($event->message->id);
        if ($response->isSucceeded()) {
           	$tempfile = "./ImageCognition/images/".$event->message->id .".png";
    		file_put_contents($tempfile, $response->getRawBody());
		exec("convert ".$tempfile."  -resize 1500x1500\< ".$tempfile);
		//$result = hanziCognitionAzure($event->message->id,$profile);
		$result = hanziCognitionGoogle($event->message->id,$profile);
		//exec("./ImageCognition/hanzi_cognition.php .".$tempfile,$result);
		$reads = [];
		for($j=0;$j<sizeof($result[0]);$j++){	//配列の各データをPinyinになおして画像埋め込みしたい
			$reads []= strHanziRead(strHanziOnly($result[0][$j]),false,$profile,true,false,true);
		}
		
		$received = implode($result[0]);
		file_put_contents($tempfile.".txt",$received);
		
		$MessageBuilder = baseBehavior($MessageBuilder,$received,$profile,"image");
	//	$MessageBuilder->add($MessageBuilder_part);
		
		overWrite($result[0],$result[1],$reads,$event->message->id);//テキスト，位置，発音，画像
		//syslog(LOG_EMERG,print_r($httppath."ImageCognition/images/".$event->message->id."_ow.png",true));
		$MessageBuilder_part = new ImageMessageBuilder($httppath."ImageCognition/images/".$event->message->id."_ow.png",$httppath."ImageCognition/images/".$event->message->id."_th.png");
		$MessageBuilder->add($MessageBuilder_part);
	}
		//$MessageBuilder_part = new TextMessageBuilder("画像アップロード失敗");
    }elseif("video" == $event->message->type){
    }elseif("audio" == $event->message->type){
    }elseif("sticker" == $event->message->type){
	$MessageBuilder_part = command_carousel($profile);
	$MessageBuilder->add($MessageBuilder_part);
	#$MessageBuilder_part =  new TextMessageBuilder("↑応答設定機能一覧↑");
	#$MessageBuilder->add($MessageBuilder_part);
    }elseif("location" == $event->message->type){//latitude longitude
	$lat=$event->message->latitude;
	$lon=$event->message->longitude;
	if(mb_strstr($event->message->address,",")){//カンマを含む場合は英語表記
	  exec("./GetAddress/get_address.php ".$lat." ".$lon,$location);
	}else{$location[0]=$event->message->address;}//英語表記じゃなければそのまま
	if($location[0]!=""){$MessageBuilder = baseBehavior($MessageBuilder,$location[0],$profile,"location");}
    }
} elseif("postback" == $event->type){
    $postbackeddata=explode("?",$event->postback->data);
    $MessageBuilder = altByPostback($MessageBuilder,$postbackeddata[0],$postbackeddata[1],$profile);
} elseif ("follow" == $event->type) {        //お友達追加時    
    $MessageBuilder = new MultiMessageBuilder();	
    $MessageBuilder_part = modUserAttr($profile);
    $MessageBuilder->add($MessageBuilder_part);
    $MessageBuilder_part = new TextMessageBuilder("お友達追加ありがとうございます．このBotは中国語を効率的に学ぶためのLINEbotです．まず，あなたの利用方法を教えてください．\n各種機能や設定を行うときには何かスタンプを送ってみてください．漢字を含むテキストや画像を送ることで，設定に応じて発音符号などが帰ってきます．とにかくスタンプを送ってみてください！");
    $MessageBuilder->add($MessageBuilder_part);
    exec("linetxt_uri.sh ".urlencode("友達追加されました//".$profile["id"]."//".$profile["displayName"]."//".$profile["statusMessage"]."//".$profile["pictureUri"]));
} elseif ("join" == $event->type) {           //グループに入ったときのイベント
    $MessageBuilder= new TextMessageBuilder('グループご招待ありがとうございます．');
    exec("linetxt_uri.sh ".urlencode("グループ招待されました by ".$profile["id"]));
} elseif("leave" == $event->type){
  exec("linetxt_uri.sh ".urlencode("グループ退出させられました by ".$profile["id"]));//退出させられた
} elseif("unfollow" == $event->type){
   exec("linetxt_uri.sh ".urlencode("ブロックされました by ".$profile["id"]));
} elseif("beacon" == $event->type){
	//$MessageBuilder_part = new TextMessageBuilder($event->beacon->type);
    	//$MessageBuilder->add($MessageBuilder_part);

}else{
   exec("linetxt_uri.sh ".urlencode("未知のメッセージ ".$profile["id"]));
}

if($MessageBuilder!=null){
	$response = $bot->replyMessage($event->replyToken, $MessageBuilder);
	//syslog(LOG_EMERG,print_r($response,true));
}
//syslog(LOG_EMERG, print_r($MessageBuilder, true));
return;
}
function baseBehavior($MessageBuilder,$received,$profile,$from){
include $profile["lang"]<2 ? "./TextData.txt" : ($profile["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");


switch($profile["base"]){
	case 0:
		//syslog(LOG_EMERG,print_r(getInfo($profile["id"],"lang"),true));
		if($from=="image")
			$result=strHanziRead(strHanziOnly($received),false,$profile,true,false);
		else
			$result=strHanziRead($received,false,$profile,false);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
		loggingInput($profile["id"],$currentmode,$received,strHanziOnly($received));//ログ
		break;
	case 1:
		$langforwiki=[["ja","wiki"],["ja","wiki"],["zh","wiki"],["zh","zh-hant"]];
		$MessageBuilder_part = new TextMessageBuilder("https://".$langforwiki[$profile["lang"]][0].".m.wiktionary.org/".$langforwiki[$profile["lang"]][1]."/".mb_substr($received,0,1));
		$MessageBuilder->add($MessageBuilder_part);
		break;
	case 2:
		$result=strHanziRead($received,true,$profile,true,true);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
	//	syslog(LOG_EMERG,print_r(getInfo($profile["id"],"lang"),true));
		break;
	case 4://単語クイズ
		sendQuizMean(strHanziOnly($received),$profile);
	
		break;
	case 6:	//繁体字簡体字変換
		if($from=="image"){$received = strHanziOnly($received);}
		$MessageBuilder_part = new TextMessageBuilder(transSimpTrad($received,$profile)); 
		$MessageBuilder->add($MessageBuilder_part);
		break;
	case 7:	//音声に変換
		$result=strHanziRead($received,false,$profile,false);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
		//まず読み方を表示してそのあと音声
		$UriDur = generateVoice(mb_substr($received,0,1000),$userid);//フルURIにする
		if(!$UriDur[0]==""){
		$MessageBuilder_part = new AudioMessageBuilder($UriDur[0],$UriDur[1]*1000);
		$MessageBuilder->add($MessageBuilder_part);
		}
		break;
	case 11://フィードバック
		exec("linetxt.sh "."\"=BOTからの送信：".$received."\"");
		$MessageBuilder_part = new TextMessageBuilder($messafterfeedback);
		$MessageBuilder->add($MessageBuilder_part);
		altInfo($profile["id"],"base",0);
		break;
	default://ケース12以降は他言語への変換である
		$othermodes=["Cantonese","Korean","JapaneseHiragana","JapaneseOnKun","Vietnamese"];

		$MessageBuilder_part = new TextMessageBuilder(transOtherStr($received,$othermodes[$profile["base"]-12]));
		$MessageBuilder->add($MessageBuilder_part);
		break;
	}//end of switch
	return $MessageBuilder;
}

function altByPostback($MessageBuilder,$alttype,$altdata,$profile){
	include $profile["lang"]<2 ? "./TextData.txt" : ($profile["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");

	switch($alttype){
	case "ALTINFO":
		$OutPutMode=explode("=",$altdata)[1];
		altInfo($profile["id"],"lang",$OutPutMode);
		$MessageBuilder_part =  new TextMessageBuilder($OutPutModes_altinfo[($OutPutMode)]);
		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "ALTCHAR":
		$OutPutMode=explode("=",$altdata)[1];
		altInfo($profile["id"],"char",$OutPutMode);
		$MessageBuilder_part =  new TextMessageBuilder($OutPutModes_altchar[$OutPutMode]);
		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "USERCONF":
		$MessageBuilder_part = modUserAttr($profile);
    		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "OTHERS":
		$MessageBuilder_part = others_carousel($profile);
    		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "RESULT":
		$MessageBuilder_part = new TextMessageBuilder(showLearnt($profile));
		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "CHARCONF":
		$MessageBuilder_part = modCharAttr($profile);
    		$MessageBuilder->add($MessageBuilder_part);
		break;
	case "BASE":

		$postbacked_parameter=explode("=",$altdata)[1];
		altInfo($profile["id"],"base",$postbacked_parameter);	
		$MessageBuilder_part =  new TextMessageBuilder($actions_message_pattern[$postbacked_parameter]);
		$MessageBuilder->add($MessageBuilder_part);
		break;	
	case "MEAN":
		if(explode("=",$altdata)[1]=="NULL"){
			$MessageBuilder =  sendQuizMean($profile,null);
		}else{
			$data= explode("&",$altdata);
			$word = explode("=",$data[0])[1];
			$ans = explode("=",$data[1])[1];
			$iscorrect = explode("=",$data[2])[1] == "True";
			$limit = explode("=",$data[3])[1];
			if($limit>time()){
				loggingLearntWord($profile,$word,($iscorrect ? 1 : 0));
				$MessageBuilder_part = new TextMessageBuilder($ans.": ". ($iscorrect ? $correct_message : $incorrect_message));
				$MessageBuilder->add($MessageBuilder_part);
			}else{	
				$MessageBuilder_part = new TextMessageBuilder($answer_1min);
				$MessageBuilder->add($MessageBuilder_part);

			}
		}
		break;
	case "PRO":
		if(explode("=",$altdata)[1]=="NULL"){
			$MessageBuilder =  sendQuizRead($profile);
		}else{
			$data= explode("&",$altdata);
			$hanzi = explode("=",$data[0])[1];
			$ans = explode("=",$data[1])[1];
			$iscorrect = explode("=",$data[2])[1] == "True";
			$limit = explode("=",$data[3])[1];
			if($limit>time()){
				loggingLearntHanzi($profile,$hanzi,($iscorrect ? 1 : 0));
				$MessageBuilder_part = new TextMessageBuilder($ans.": ". ($iscorrect ? "正解" : "不正解"));
				$MessageBuilder->add($MessageBuilder_part);
			}else{	
				$MessageBuilder_part = new TextMessageBuilder("1分間以内に回答してください");
				$MessageBuilder->add($MessageBuilder_part);
			}
		}
		break;
	}

	return $MessageBuilder;
}
