<?php

function showLearnt($userinfo){//台湾の
	$pdo = new PDO("mysql:host=localhost;dbname=Hanzi","root","yuji2943");
	$query = "select distinct hanzi,level from learnthanzi where ID=\"".$userinfo["id"]."\" and level>0  order by level desc";

	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$returnstr="あなたの学習状況を表示します．右側の数字は記憶レベルとして正解回数を示しており，5回正解すると出題されません．\n";
	$returnstr.="漢字発音記憶レベル\n";
	for($i=0;$i<sizeof($result);$i++){
		$returnstr.="\"".$result[$i]["hanzi"]."\" ".$result[$i]["level"]."\n";
	}
	$query = "select distinct word,level from learntword where ID=\"".$userinfo["id"]."\" order by level desc";
	//return;
	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$returnstr.="単語意味記憶レベル\n";
	for($i=0;$i<sizeof($result);$i++){
		$returnstr.="\"".$result[$i]["word"]."\" ".$result[$i]["level"]."\n";
	}
	
	$stmt->closeCursor(); // this is not even required
	return $returnstr;
}


