#!/usr/bin/php
<?php
function hanziCognitionGoogle($source,$profile){
include __DIR__."/apikey_google.php";// APIキーは別ファイルで
//print $api_key;
	//入力
$referer = "nobuta.xyz/*" ;
// 画像へのパス
$image_path = __DIR__."/images/".$source.".png";//"./menkyo.png" ;
// リクエスト用のJSONを作成
$json = json_encode( array(
	"requests" => array(
		array(
			"image" => array(
				"content" => base64_encode( file_get_contents( $image_path ) ) ,
			) ,
			"features" => array(
				/*
				array(
					"type" => "FACE_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				array(
					"type" => "LANDMARK_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				array(
					"type" => "LOGO_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				array(
					"type" => "LABEL_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				*/
				array(
					"type" => "TEXT_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				/*
				array(
					"type" => "SAFE_SEARCH_DETECTION" ,
					"maxResults" => 3 ,
				) ,
				array(
					"type" => "IMAGE_PROPERTIES" ,
					"maxResults" => 3 ,
				) ,
					*/
			) ,
			"imageContext"=>array(
					"languageHints" => array("zh-CN","zh-TW","ja")
				),	
		) ,
	) ,
) ) ;

// リクエストを実行
$curl = curl_init() ;
curl_setopt( $curl, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key ) ;
curl_setopt( $curl, CURLOPT_HEADER, true ) ; 
curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" ) ;
curl_setopt( $curl, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) ) ;
curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;
if( isset($referer) && !empty($referer) ) curl_setopt( $curl, CURLOPT_REFERER, $referer ) ;
curl_setopt( $curl, CURLOPT_TIMEOUT, 15 ) ;
curl_setopt( $curl, CURLOPT_POSTFIELDS, $json ) ;
$res1 = curl_exec( $curl ) ;
$res2 = curl_getinfo( $curl ) ;
curl_close( $curl ) ;

// 取得したデータ
$json = substr( $res1, $res2["header_size"] ) ;				// 取得したJSON
$header = substr( $res1, 0, $res2["header_size"] ) ;		// レスポンスヘッダー

$ar=json_decode($json,true);
$labels=[];
for($i=0;$i<sizeof($ar["responses"][0]["textAnnotations"]);$i++){
	  $labels[]=trim($ar["responses"][0]["textAnnotations"][$i]["description"]);
}
return $labels;
}
