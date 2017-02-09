<?php

function showLearnt($userinfo){//台湾の
	$pdo = new PDO("mysql:host=localhost;dbname=Hanzi","root","yuji2943");
	$query = "select distinct hanzi,level from learnthanzi where ID=\"".$userinfo["id"]."\"";
	//return;
	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$returnstr="漢字学習履歴\n";
	for($i=0;$i<sizeof($result);$i++){
		$returnstr.=$result[$i]["hanzi"]." ".$result[$i]["level"]."\n";
	}
	$query = "select distinct hanzi,level from learntword where ID=\"".$userinfo["id"]."\"";
	//return;
	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$returnstr.="漢字学習履歴\n";
	for($i=0;$i<sizeof($result);$i++){
		$returnstr.=$result[$i]["hanzi"]." ".$result[$i]["level"]."\n";
	}



	$stmt->closeCursor(); // this is not even required
	return $returnstr;
}


