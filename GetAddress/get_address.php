#!/usr/bin/php
<?php
include "apikey.php";
if($argc<3){
	print "";
	return;
}
$x = $argv[1]; 
$y = $argv[2];

//もし日本語設定で郵便番号が含まれていればそこは日本であり適切に表示されている
//そうでなければ中国語圏であるので中国語設定を利用する

$lang = "ja";
$StringaCo= file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='. $x.','.$y.'&location_type=ROOFTOP&language='.$lang.'&key='.$apikey);
$decoded = json_decode($StringaCo,true);
if(isset($decoded["results"][0]["formatted_address"])){
$ja_result=($decoded["results"][0]["formatted_address"]);
if(mb_strstr($ja_result,"〒")!=FALSE){
	print $ja_result;
	return;
}
}

$lang = "zh";
$StringaCo= file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='. $x.','.$y.'&location_type=ROOFTOP&language='.$lang.'&key='.$apikey);
$decoded = json_decode($StringaCo,true);
if(isset($decoded["results"][0]["formatted_address"])){
$zh_result=( $decoded["results"][0]["formatted_address"]);
if(mb_strlen($zh_result)<5)
	print "";
else{
	print ($zh_result);
}
}else{
	print "";
}


