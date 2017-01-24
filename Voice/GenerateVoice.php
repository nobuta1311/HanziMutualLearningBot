<?php

//generateVoice("政治大學","12345");
function generateVoice ($text,$id){
require_once("./Voice/apikey.php");
for($j=0;$j<mb_strlen($text);$j++){	//漢字かどうか
	$acode= strtoupper(substr(json_encode(mb_substr($text,$j,1)),3,4));
	if(mb_substr($inputstr,$j,1)!="#" and (hexdec($acode)<hexdec("4E00") || hexdec($acode)>hexdec("9FA5"))){//漢字でない場合
		if($j==mb_strlen($text)-1)return(["",0]);
	}else{//漢字の場合
		break;
	}
}

$url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
$headers = array(
	"Content-Length: 0",
	"Ocp-Apim-Subscription-Key: ".$azure_speech_token,
    //'Authorization: Basic '.base64_encode('user:pass'),//ベーシック認証
);
$options = array('http' => array(
    'method' => 'POST',
 //   'content' => http_build_query($data),
    'header' => implode("\r\n", $headers),
));
$token = file_get_contents($url, false, stream_context_create($options));

//print $token;
//$token = base64_encode($token);
//ここから２つめ
$curl = curl_init();
$url = "https://speech.platform.bing.com/synthesize";
curl_setopt($curl,CURLOPT_URL,$url);
$data = array(
    'VoiceType' => 'Female',
    'VoiceName' => 'Microsoft Server Speech Text to Speech Voice (zh-TW, Yating, Apollo)',
    'Locale' 	=> 'zh-TW',
    'OutputFormat'=>'Audio16khz128kbitrateMonoMp3',
    'RequestUri'=>'https://speech.platform.bing.com/synthesize',
    'Text'=>$text,
);
$data_ssml="<speak version='1.0' xml:lang='".$data["Locale"]."'><voice xml:lang='".$data["Locale"]."' xml:gender='".$data["VoiceType"]."' name='".$data["VoiceName"]."'>".$data["Text"]."</voice></speak>";
//print $data_ssml;
$headers = array(
	"Content-Type: application/ssml+xml",
	"Content-Length: ".strlen($data_ssml),
	"X-Microsoft-OutputFormat: audio-16khz-128kbitrate-mono-mp3",
        "Authorization: Bearer ".$token,
	"X-Search-AppId: D4D5267291D74C748AD842B1D98141A5",
	"X-Search-ClientID: 1ECFAE91408841A480F00935DC390960",
	"User-Agent: COMMANDLINE",
);
curl_setopt($curl, CURLOPT_POST, 'TRUE'); // post
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_ssml);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); // リクエストにヘッダーを含める
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
$options = array('http' => array(
    'method' => 'POST',
    'data' => $data_ssml,
    'header' => implode("\r\n", $headers),
));
$response = curl_exec($curl);

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); 
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
$result = json_decode($body, true); 
curl_close($curl);
$filename=$id.time();
$fullpath = __DIR__."/PastVoices/".$filename;
file_put_contents($fullpath.".mp3",$body);
exec("ffmpeg -i ".$fullpath.".mp3 -c:a libfdk_aac -b:a 128k ".$fullpath.".m4a");
exec("ffprobe -show_streams  ".$fullpath.".m4a 2>/dev/null |grep duration=",$proberesult);
return ["https://nobuta.xyz/HanziMutualLearningBot/Voice/PastVoices/".$filename.".m4a",explode("=",$proberesult[0])[1]];
}
?>
