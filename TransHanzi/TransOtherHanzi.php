<?php
#require "./Log/SendQuery.php";
function transOtherStr($input,$bigmode){
	//Cantonese,Korean,JapaneseHiragana,JapanenseOnKun,Vietnamese
	$returnstr="";
	switch($bigmode){
	case "JapaneseHiragana":
		require "./JapaneseToHiragana/JapaneseToHiragana.php";
		$returnstr= japaneseToHiragana($input);
		break;
	case "JapaneseOnKun":
		//syslog(LOG_EMERG,print_r($returnstr,true));
		//新字体変換（未実装)
		for($i=0;$i<mb_strlen($input);$i++){
		$returnstr.=transOtherHanzi(mb_substr($input,$i,1),null,"OnKun");
		}
		break;
	case "Cantonese":
		//繁体字に変換する機能が完成したらここに
		for($i=0;$i<mb_strlen($input);$i++){
		$returnstr.=explode(" ",transOtherHanzi(mb_substr($input,$i,1),null,"Cantonese"))[0];
		}
		break;
	case "Korean":
		for($i=0;$i<mb_strlen($input);$i++){
		$returnstr.=transOtherHanzi(mb_substr($input,$i,1),null,"Hangul");
		}
		$returnstr.="\n";
		for($i=0;$i<mb_strlen($input);$i++){
		$returnstr.=transOtherHanzi(mb_substr($input,$i,1),null,"Korean");
		}
		break;
	case "Vietnamese":
		for($i=0;$i<mb_strlen($input);$i++){
		$returnstr.=transOtherHanzi(mb_substr($input,$i,1),null,"Vietnamese");
		}
		break;
	}
	return $returnstr;
}
function transOtherHanzi($inputchar,$inputcharcode,$mode){//単一文字とする 
//userinfoは
	$query="";
//広東語発音Cantonese,韓国語発音Korean，ハングルHangul，日本語漢字Japanese，ひらがなHiragana，ベトナム語発音Vietnamese
	if($inputchar==null)
		$query="select * from reading where char_code=\"".$inputcharcode."\"";
	else
		$query="select * from reading where achar=\"".$inputchar."\"";
	$result = sendQuery($query);
	if(!isset($result[0]))return null;
	switch($mode){
	case "Cantonese":
		if(isset($result[0][$mode]))
			return $result[0][$mode];
		break;
	case "Korean":
		if(isset($result[0][$mode]))
			return $result[0][$mode];
		break;
	case "Hangul":		
		if(isset($result[0][$mode]))
			return $result[0][$mode];
		break;
	case "OnKun":
		$output_onkun="";
		if(isset($result[0]["JapaneseKun"]))
			$output_onkun.=$result[0]["JapaneseKun"]."\n";
		if(isset($result[0]["JapaneseOn"]))
			$output_onkun.=$result[0]["JapaneseOn"];
		if(mb_strlen($output_onkun)>0)
			return $output_onkun." ";
		break;
	case "Vietnamese":
		if(isset($result[0][$mode]))
			return $result[0][$mode];
		break;
	}
}

