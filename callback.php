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
require_once "./Log/LoggingInput.php";
require_once "./Learning/SendQuiz.php";
require_once "./Log/SendQuery.php";
//require_once "./ImageCognition/HanziCognition.php";
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder as AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
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

//	syslog(LOG_EMERG,print_r(mb_substr($received,0,3),true));
	if(mb_substr($received,0,3)=="@@@"){    
		$inputbytxt = explode("?",$received);
		$MessageBuilder=altByPostback($MessageBuilder,mb_substr($inputbytxt[0],3),$inputbytxt[1],$profile);
	}else{
		$MessageBuilder=baseBehavior($MessageBuilder,$received,$profile,"text");
   	}//end of else

    }elseif("image" == $event->message->type){ 
	$response = $bot->getMessageContent($event->message->id);
        if ($response->isSucceeded()) {
           	$tempfile = "./ImageCognition/images/".$event->message->id;
    		file_put_contents($tempfile, $response->getRawBody());
		exec("./ImageCognition/hanzi_cognition.php .".$tempfile,$result);
		$received = implode(str_replace(array(";","\r\n", "\r", "\n"), '', $result));
		file_put_contents($tempfile.".txt",$received);
		
		$MessageBuilder = baseBehavior($MessageBuilder,$received,$profile,"image");
		//syslog(LOG_EMERG,print_r($result_2,true));
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
	if($location[0]!=""){$MessageBuilder = baseBehavior($MessageBuilder,$received,$profile,"location");}
    }
} elseif("postback" == $event->type){
    $postbackeddata=explode("?",$event->postback->data);
    $MessageBuilder = altByPostback($MessageBuilder,$postbackeddata[0],$postbackeddata[1],$profile);
} elseif ("follow" == $event->type) {        //お友達追加時    
    $MessageBuilder = new MultiMessageBuilder();	
    $MessageBuilder_part = modUserAttr($profile);
    $MessageBuilder->add($MessageBuilder_part);
    $MessageBuilder_part = new TextMessageBuilder("お友達追加ありがとうございます．このBotは中国語を効率的に学ぶためのLINEbotです．まず，あなたの利用方法を教えてください．\n各種機能や設定を行うときには何かスタンプを送ってみてください．");
    $MessageBuilder->add($MessageBuilder_part);
    exec("linetxt_uri.sh ".urlencode("友達追加されました//".$profile["id"]."//".$profile["displayName"]."//".$profile["statusMessage"]."//".$profile["pictureUri"]));
} elseif ("join" == $event->type) {           //グループに入ったときのイベント
    $MessageBuilder= new TextMessageBuilder('グループご招待ありがとうございます．');
    exec("linetxt_uri.sh ".urlencode("グループ招待されました by ".$profile["id"]));
} elseif("leave" == $event->type){
  exec("linetxt_uri.sh ".urlencode("グループ退出させられました by ".$profile["id"]));//退出させられた
} elseif("unfollow" == $event->type){
   exec("linetxt_uri.sh ".urlencode("ブロックされました by ".$profile["id"]));
} else {					//ブロックされた
   exec("linetxt_uri.sh ".urlencode("ブロックされました by ".$profile["id"]));
}

if($MessageBuilder!=null){
	$response = $bot->replyMessage($event->replyToken, $MessageBuilder);
}
//syslog(LOG_EMERG, print_r($MessageBuilder, true));
return;
}
function baseBehavior($MessageBuilder,$received,$profile,$from){
    if($profile["lang"]>1){
    include "./TextData_TW.txt";
    }else{
    include "./TextData.txt";
    }

$MessageBuilder = new MultiMessageBuilder();	//メッセージ用意    

switch($profile["base"]){
	case 0:
		//syslog(LOG_EMERG,print_r(getInfo($profile["id"],"lang"),true));
		if($from=="image")
			$result=strHanziRead($received,false,$profile,true);
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
	case 3:
		$result = sendQuiz(strHanziOnly($received),$profile);
		if(sizeof($result[0])==0){
			$MessageBuilder_part =  new TextMessageBuilder($message_noquiz);
			$MessageBuilder->add($MessageBuilder_part);
		}else{
		for($i=0;$i<sizeof($result[0]);$i++){
			shuffle($result[1][$i]);
 			$label=[$result[1][$i][0],$result[1][$i][1],$result[1][$i][2],$result[1][$i][3]];
		//	syslog(LOG_EMERG,print_r($result[1],true));
    			for($j=0;$j<4;$j++) {
    				$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$j], "PRO?char=".$result[0][$i]."&ans=".$result[1][$i][$j]);
    			}
			$button = new TemplateBuilder\ButtonTemplateBuilder(" \"".$result[0][$i]."\"".$button_forquiz1,$button_forquiz2,null,$actions);	
				$MessageBuilder_part = new TemplateMessageBuilder($result[0][$i].$button_forquizpc,$button);
				$MessageBuilder->add($MessageBuilder_part);

			}
		}
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
	//actions_message_pattern，OutPutModesの読み込み
	if($profile["lang"]>1){
    		include "./TextData_TW.txt";
    	}else{
    		include "./TextData.txt";
     	}

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
	case "PRO":
		$data= explode("&",$altdata);
		$hanzi = explode("=",$data[0])[1];
		$ans = explode("=",$data[1])[1];
		$tempans = searchFromReading($hanzi,null,$profile);
		if($profile["char"]==1){
			$tempans=pinyinToBpmf(charPinyin($tempans));
		}
		if($ans == $tempans){
			loggingLearntHanzi($profile["id"],$hanzi,0,5);
			$MessageBuilder_part =  new TextMessageBuilder($mess_correct);
			$MessageBuilder->add($MessageBuilder_part);
		}else{
			loggingLearntHanzi($profile["id"],$hanzi,0,1);
			$MessageBuilder_part =  new TextMessageBuilder($mess_uncorrect);
			$MessageBuilder->add($MessageBuilder_part);
		}
		break;
	default:
		$MessageBuilder_part = new TextMessageBuilder("エラー：意図しないPostBackが送信されました");
		$MessageBuilder->add($MessageBuilder_part);
		break;
	}
	return $MessageBuilder;
}
