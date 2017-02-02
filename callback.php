<?php
require_once './lineapikey.php';
require_once __DIR__ . '/../line/vendor/autoload.php';
require_once "./command_carousel.php";
require_once "./ModUserAttr.php";
require_once "./UserControl.php";
require_once "./TransHanzi/TransSimpTrad.php";
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

    $currentmode = getInfo($profile["id"],"base");
    if ("text" == $event->message->type) {
	$received = $event->message->text;
	$received = str_replace(array("\r\n", "\r", "\n"), '', $received);

//	syslog(LOG_EMERG,print_r(mb_substr($received,0,3),true));
	if(mb_substr($received,0,3)=="@@@"){
		$inputbytxt = explode("?",$received);
		$MessageBuilder=altByPostback(mb_substr($inputbytxt[0],3),$inputbytxt[1],$profile);
	}elseif($currentmode==0){
		//syslog(LOG_EMERG,print_r(getInfo($profile["id"],"lang"),true));
		$result=strHanziRead($received,false,getInfo($profile["id"],"lang"),false);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
		loggingInput($profile["id"],$currentmode,$received,strHanziOnly($received));//ログ
	}elseif($currentmode==2){	//意味も表示する
		$result=strHanziRead($received,true,getInfo($profile["id"],"lang"),true,true);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
	}elseif($currentmode==3){//入力に対してクイズを出す
		$result = sendQuiz(strHanziOnly($received),getInfo($profile["id"],"lang"));
		if(sizeof($result[0])==0){
			$MessageBuilder_part =  new TextMessageBuilder("出題候補がありません．");
			$MessageBuilder->add($MessageBuilder_part);
		}else{
		for($i=0;$i<sizeof($result[0]);$i++){
			shuffle($result[1][$i]);
 			$label=[$result[1][$i][0],$result[1][$i][1],$result[1][$i][2],$result[1][$i][3]];
		//	syslog(LOG_EMERG,print_r($result[1],true));
    			for($j=0;$j<4;$j++) {
    				$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$j], "PRO?char=".$result[0][$i]."&ans=".$result[1][$i][$j]);
    			}
			$button = new TemplateBuilder\ButtonTemplateBuilder(" \"".$result[0][$i]."\" の発音は？","以下より選択してください",null,$actions);	
//$confirm = new ConfirmTemplateBuilder($result[0][$i]."の発音は".$result[1][$i],[$action_yes,$action_no]);
				$MessageBuilder_part = new TemplateMessageBuilder($result[0][$i]."の発音の確認",$button);
				$MessageBuilder->add($MessageBuilder_part);

			}
		}

	}elseif($currentmode==6){
		$MessageBuilder_part = new TextMessageBuilder(transSimpTrad($received));
		$MessageBuilder->add($MessageBuilder_part);
	}elseif($currentmode==7){	
		$result=strHanziRead($received,false,getInfo($profile["id"],"lang"),false);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
		//まず読み方を表示してそのあと音声
		$UriDur = generateVoice(mb_substr($received,0,1000),$userid);//フルURIにする
		if(!$UriDur[0]==""){
		$MessageBuilder_part = new AudioMessageBuilder($UriDur[0],$UriDur[1]*1000);
		$MessageBuilder->add($MessageBuilder_part);
		}
	}elseif($currentmode==8){
		exec("linetxt.sh "."\"=BOTからの送信：".$received."\"");
		$MessageBuilder_part = new TextMessageBuilder("送信されました．\n設定は発音の参照に変更されました．");
		$MessageBuilder->add($MessageBuilder_part);
		altInfo($profile["id"],"base",0);	
		
	}elseif($currentmode==1){	//１漢字
		$lang_info = getInfo($profile["id"],"lang");
		$langset=[["ja","wiki"],["ja","wiki"],["zh","wiki"],["zh","zh-hant"]];
		$MessageBuilder_part = new TextMessageBuilder("https://".$langset[$lang_info][0].".m.wiktionary.org/".$langset[$lang_info][1]."/".mb_substr($received,0,1));
		$MessageBuilder->add($MessageBuilder_part);
	}
	//syslog(LOG_EMERG,print_r(transSimpTrad($received),true));


    }elseif("image" == $event->message->type){ 
	$response = $bot->getMessageContent($event->message->id);
        if ($response->isSucceeded()) {
           	$tempfile = "./ImageCognition/images/".$event->message->id;
    		file_put_contents($tempfile, $response->getRawBody());
		exec("./ImageCognition/hanzi_cognition.php .".$tempfile,$result);
		$received = implode(str_replace(array(";","\r\n", "\r", "\n"), '', $result));
		file_put_contents($tempfile.".txt",$received);
		if($currentmode==2){
			$result_2=strHanziread($received,true,getInfo($profile["id"],"lang"),true,true);}
		else{
			$result_2=strHanziread($received,true,getInfo($profile["id"],"lang"),true,false);}
			
		//syslog(LOG_EMERG,print_r($result_2,true));
		$MessageBuilder_part =  new TextMessageBuilder($result_2);
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
	$MessageBuilder_part =  new TextMessageBuilder("↑応答設定機能一覧↑");
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
		$result=strHanziRead($location[0],false,getInfo($profile["id"],"lang"),false);
		$MessageBuilder_part =  new TextMessageBuilder($result);
		$MessageBuilder->add($MessageBuilder_part);
	}
    }else {
	$MessageBuilder_part = command_carousel();
//	syslog(LOG_EMERG, print_r($MessageBuilder_part, true));
	$MessageBuilder->add($MessageBuilder_part);
    }
} elseif("postback" == $event->type){
	$postbackeddata=explode("?",$event->postback->data);
	$MessageBuilder = altByPostback($postbackeddata[0],$postbackeddata[1],$profile);

} elseif ("follow" == $event->type) {        //お友達追加時    
    $MessageBuilder = new MultiMessageBuilder();	
   $MessageBuilder_part = modUserAttr();
    $MessageBuilder->add($MessageBuilder_part);
    $MessageBuilder_part = new TextMessageBuilder("お友達追加ありがとうございます．このBotは中国語を効率的に学ぶためのLINEbotです．まず，あなたの利用方法を教えてください．\n各種機能や設定を行うときには何かスタンプを送ってみてください．");
    $MessageBuilder->add($MessageBuilder_part);

    addUser($profile["id"]);
    exec("notify ".$profile["id"]);
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
//syslog(LOG_EMERG, print_r($MessageBuilder, true));
return;

function altByPostback($alttype,$altdata,$profile){
	$MessageBuilder = new MultiMessageBuilder();	//メッセージ用意    
	
	if($alttype=="ALTINFO"){
		$OutPutModes=["注音モードに変更しました","拼音モードに変更しました"]; 
		$OutPutMode=explode("=",$altdata)[1];
		altInfo($profile["id"],"lang",$OutPutMode);
		$MessageBuilder_part =  new TextMessageBuilder($OutPutModes[($OutPutMode+1)%2]);
		$MessageBuilder->add($MessageBuilder_part);
	}elseif($alttype=="USERCONF"){	
		$MessageBuilder_part = modUserAttr();
    		$MessageBuilder->add($MessageBuilder_part);
	}elseif($alttype=="BASE"){
		$actions_message_pattern=["発音の参照","漢字１文字の参照","意味と発音の参照","クイズを開始","学習状況画像","記録済み漢字一覧","簡体字繁体字相互変換","音声確認","フィードバック\n次に送るメッセージは開発者に届きます．","ユーザ設定変更","発音記号種類変更","リセット"];//12個

		$postbacked_parameter=explode("=",$altdata)[1];
		altInfo($profile["id"],"base",$postbacked_parameter);	
		$MessageBuilder_part =  new TextMessageBuilder($actions_message_pattern[$postbacked_parameter]);
		$MessageBuilder->add($MessageBuilder_part);

	}
	elseif($alttype=="PRO"){ //char&ans
		$data= explode("&",$altdata);
		$hanzi = explode("=",$data[0])[1];
		$ans = explode("=",$data[1])[1];
		$tempans = searchFromReading($hanzi,null,getInfo($profile["id"],"lang"));
		if($ans == $tempans){
			loggingLearntHanzi($profile["id"],$hanzi,0,5);
		}else{
			loggingLearntHanzi($profile["id"],$hanzi,0,1);
		}

	}

	return $MessageBuilder;
}
