<?php
require "./Log/SendQuery.php";
function transSimpTrad($inputstr,$userinfo){	//vec=0 なら簡体字から繁体字
	$output=[];

	for($i=0;$i<mb_strlen($inputstr);$i++){
		$input = strtoupper(substr(json_encode(mb_substr($inputstr,$i,1)),3,4));
		if($userinfo["lang"]%2==0){
			$query = "select res from transtosimp where ind=\"U+".$input."\"";//簡体字用
		}else{
			$query = "select res from transtotrad where ind=\"U+".$input."\"";
		}
		$result = sendQuery($query);
		if(isset($result[0])){$output[]=rehan(mb_substr($result[0]["res"],2));}
		else{$output[]=mb_substr($inputstr,$i,1);}
	}
	return implode("",$output);	
}
