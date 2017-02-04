<?php
require "./Log/SendQuery.php";
function transSimpTrad($input){
	$output=[];
	for($i=0;$i<mb_strlen($input);$i++){
		$query = "select res from transchar where ind=\"".mb_substr($input,$i,1)."\"";
		$result = sendQuery($query);
		if(isset($result[0])){$output[]=$result[0]["res"];}
		else{$output[]=mb_substr($input,$i,1);}
	}
	return implode("",$output);	
}
