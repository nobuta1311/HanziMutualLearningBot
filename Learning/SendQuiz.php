<?php
//何度もユーザとやりとりする必要があるがどうする？
//まとめてボタンを５つ出して，それぞれ独立にクイズとして反応する
require_once __DIR__."/../HanziPronunciation/HanziPinyin.php";
require_once __DIR__."/../HanziPronunciation/pinyin_bpmf.php";
require_once __DIR__."/../Log/SendQuery.php";

use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder as MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;

function sendQuizMean($userinfo,$answord){
	include $userinfo["lang"]<2 ? "./TextData.txt" : ($userinfo["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");

//出すクイズを決める 5個まで
	$MessageBuilder = new MultiMessageBuilder();
	$PROBNUMMAX=1;
	$charnum=0;
	if(isset($userinfo["hskrank"])){
		$rank=$userinfo["hskrank"];
	}else{
		$rank=1;
	}
	$query_past = "select * from HSK where rank=".$rank." order by rand() limit 4;";
	$res = sendQuery($query_past);
	$target=array();
	$meaning=array();
	for($i=0;$i<4;$i++){
		$target[$i]=$res[$i]["hanzi"];
		$meaning[$i]=$res[$i]["meaning"];
	}
	$result = [$target,$meaning];

	$label=[$result[1][0],$result[1][1],$result[1][2],$result[1][3]];
	//	syslog(LOG_EMERG,print_r($result[1],true));
   	for($j=0;$j<4;$j++) {
    		$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$j], "MEAN?hanzi=".($j+1));
    	}

	$button = new TemplateBuilder\ButtonTemplateBuilder(" \"".$result[0][0]."\"".$button_forquiz_mean1,$button_forquiz_mean2,null,$actions);	
	$MessageBuilder_part = new TemplateMessageBuilder($result[0][0].$button_forquizpc_mean,$button);
	$MessageBuilder->add($MessageBuilder_part);
	return $MessageBuilder;
}

//発音クイズ
function sendQuizRead($inputstr,$userinfo){
//出すクイズを決める 5個まで
$PROBNUMMAX=1;
$charnum=0;
if($inputstr==null){	//過去のデータから決めるとき
	$query_past = "select * from learnthanzi order by rand() where level<5 limit ".$PROBNUMMAX.";";
	$res_past = sendQuery($query_past);
	$charnum = sizeof($res_past);
	$target=array();
	for($i=0;$i<$charnum;$i++){
		$target[$i]=$res_past[$i]["hanzi"];		
	}
}else{			//現在のデータから決めるとき
	$charar=array();
	for($i=0;$i<mb_strlen($inputstr);$i++){
		if(!in_array(mb_substr($inputstr,$i,1),$charar)){
			$charar[]=mb_substr($inputstr,$i,1);
		}//配列にしてシャッフル
	}
	$charnum = min($PROBNUMMAX,sizeof($charar));
	shuffle($charar);
	for($i=0;$i<$charnum;$i++){
		$target[]=$charar[$i];
	}
}
//syslog(LOG_EMERG,print_r($target,true));
//return $target;
//各漢字の正しい読み方（複数）を手に入れる
$correct=array();
for($i=0;$i<$charnum;$i++){
	$acode = strtoupper(substr(json_encode($target[$i]),3,4));
	$correct_char[$i] = searchFromReading(null,$acode,$userinfo);
	while($j<4){
		$temp = searchFromReading(null,$acode,$userinfo);
		$acode = dechex(hexdec($acode)+rand(1,10));
		if($temp!=null){
			if($userinfo["char"]==1){
				$correct[$i][]=pinyinToBpmf(charPinyin($temp));
			}else{
				$correct[$i][]=$temp;
			}
			$j+=1;

		}else{
			$j+=1;
		}
	}
	
}
//syslog(LOG_EMERG,print_r($correct,true));

return [$target,$correct,$correct_char];
}

