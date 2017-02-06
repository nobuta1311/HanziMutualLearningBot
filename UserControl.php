<?php
function getProfile($event){
	$profile=array();
	if($event->source->type=="user") {
		$profile["type"]="user";
		$profile["id"]=$event->source->userId;
	}
	elseif($event->source->type=="group"){
		$profile["type"]="group";
		$profile["id"]=$event->source->groupId;

	}
	else {
		$profile["type"]="room";
		$profile["id"]=$event->source->roomId;
	}
	$profile["lang"] = getInfo($profile["id"],"lang");
	$profile["base"] = getInfo($profile["id"],"base");
	$profile["char"] = getInfo($profile["id"],"char");

	return $profile;
}
function addUser($userid){
	#ユーザが存在していなければ追加する．なおユーザ情報はidと同じ階層に追加していく．
	#現在のユーザ情報は，利用者情報(int)のみ
	$users=[];
	$json=json_decode(file_get_contents(__DIR__."/UserList.json"));
	foreach($json as $value){
		$eachrow=(array)$value;
		if($eachrow["userid"]==$userid){return 1;}
		$users[] = $eachrow;
		//ここで重複調査
	}
	$users[]=array("userid"=>$userid,"lang"=>"0","base"=>"0");
	file_put_contents(__DIR__."/UserList.json",json_encode($users));
	return 0;
}
function altInfo($userid,$place,$data){
	$json=json_decode(file_get_contents(__DIR__."/UserList.json"));
	foreach($json as $value){
		$eachrow=(array)$value;	
		if($eachrow["userid"]==$userid){$eachrow[$place]=$data;}
		$users[] = $eachrow;
	}
	file_put_contents(__DIR__."/UserList.json",json_encode($users));
	return 0;
}
function getInfo($userid,$place){	
	$json=json_decode(file_get_contents(__DIR__."/UserList.json"));
	foreach($json as $value){
		$eachrow=(array)$value;	
		if($eachrow["userid"]==$userid){
			if(isset($eachrow[$place]))
				return $eachrow[$place];
			else
				return 0;//まだ決まってなかったら取り合えず0
		}
		$users[] = $eachrow;
	}
	return 1;

}
