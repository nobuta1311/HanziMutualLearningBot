<?php
function transSimpTrad($input){
	$source_simptrad = explode("\n",file_get_contents("./TransHanzi/cjkvi-simplified.txt"));
	$database_simptrad =[];
	for($i=0;$i<sizeof($source_simptrad);$i++)
		if(mb_strlen($source_simptrad[$i])==2){
			$database_simptrad[]=[mb_substr($source_simptrad[$i],0,1),mb_substr($source_simptrad[$i],1,1)];
		}
	$output=[];
	for($i=0;$i<mb_strlen($input);$i++)
		$output[]=mb_substr($input,$i,1);
	for($i=0;$i<sizeof($database_simptrad);$i++){
		for($j=0;$j<mb_strlen($input);$j++){
			if($database_simptrad[$i][0]==mb_substr($input,$j,1)){
				$output[$j]=$database_simptrad[$i][1];
			}
		}
	}
	return implode("",$output);	
}
