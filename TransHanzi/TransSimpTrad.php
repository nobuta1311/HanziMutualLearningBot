<?php
require "./Log/SendQuery.php";
function transSimpTrad($input,$userinfo){	//vec=0 なら簡体字から繁体字
	$output=[];
	for($i=0;$i<mb_strlen($input);$i++){
		if($userinfo["lang"]%2==0){
			$query = "select res from transchar where ind=\"".mb_substr($input,$i,1)."\" order by ind!=res";//簡体字用
		}else{
			$query = "select res from transchar where ind=\"".mb_substr($input,$i,1)."\" order by ind!=res";
		}
		$result = sendQuery($query);
		if(isset($result[0])){$output[]=$result[0]["res"];}
		else{$output[]=mb_substr($input,$i,1);}
	}
	return implode("",$output);	
}
