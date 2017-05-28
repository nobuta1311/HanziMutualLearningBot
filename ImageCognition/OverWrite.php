<?php
function overWrite($text,$poly,$read,$filename){
$timestr=date("s")."\n";
//syslog(LOG_EMERG,print_r($timestr,true));

// 画像ファイルのpath
$filePathSr = __DIR__."/images/".$filename.".png";
$filePath = __DIR__."/images/".$filename."_ow.png";
$filePathTh = __DIR__."/images/".$filename."_th.png";
// フォントファイルのpath
$fontPath = "/usr/share/fonts/SOURCEHANSANSJP-NORMAL.OTF";	
$fontsize = 60;
$draw = new ImagickDraw();
// フォントの指定
$draw->setFont( $fontPath );
// フォントサイズの指定
$draw->setFontSize($fontsize);
//$draw->setStrokeWidth(0.5);
$draw->setFillColor("black");
$draw->setTextUnderColor("rgba(255,255,255,0.1");
//$draw->setstrokeColor("white");
// 画像の読み込み
$templateImg = new Imagick( $filePathSr );
$tan = [];
$incline = [];
$point = [];//各ブロックの左上x,yと右下端のx,yを取得する.サイズも
$point[0] = 40;
//フォントサイズを小さくする
for($i=1;$i<sizeof($text)-1;$i++){
	$timestr.=$i.":".date("s")." ";
	//syslog(LOG_EMERG,print_r($timestr,true));
	if($fontsize>40)$fontsize=40;
	if($read[$i]==""){$point[$i]=$point[$i-1];$fontsize+=10;continue;}
	$draw->setFontSize($fontsize);
	$metric = $templateImg->queryFontMetrics($draw,$read[$i]);
	//角度の決定 縦と横で区別
	if(($poly[$i][1]["x"] > $poly[$i][3]["x"]) and  ($poly[$i][3]["y"] > $poly[$i][1]["y"])){//横長の時
		$tan[$i] = abs(rad2deg(atan(($poly[$i][1]["y"]-$poly[$i][0]["y"])/($poly[$i][1]["x"]-$poly[$i][0]["x"]))));
		$diffx = $poly[$i][0]["x"]+$metric['textWidth']- $poly[$i+1][0]["x"];
		$diffy = $poly[$i][0]["y"]+$metric['textHeight']- $poly[$i+1][0]["y"];
		$incline[$i]=false;
	}else{
		$tan[$i] = abs(rad2deg(atan(($poly[$i][0]["y"]-$poly[$i][1]["y"])/($poly[$i][0]["x"]-$poly[$i][1]["x"]))));
		$diffx = $poly[$i][3]["x"]+$metric['textHeight']- $poly[$i+1][3]["x"]; 
		$diffy = $poly[$i][3]["y"]+$metric['textWidth']-$poly[$i+1][3]["y"];
		$incline[$i]=true;
	}
	//syslog(LOG_EMERG,print_r($diffx." ".$diffy." ".$read[$i],true));

	
	//syslog(LOG_EMERG,print_r($fontsize." ".$diffx." ".$diffy." ".$metric["textWidth"]." ".$metric["textHeight"]." ".$read[$i],true));
	if($diffx>0  and $diffy>0 ){
	//かぶったら
		$fontsize-=2;
		//小さくなりすぎたらもしくはほぼ同じ位置
		if($fontsize<13 or ($diffx <5 and $diffy<5)){
	//		syslog(LOG_EMERG,print_r($fontsize." ".$diffx." ".$diffy." ".$metric["textWidth"]." ".$metric["textHeight"]." ".$read[$i],true));

			$point[$i]=$fontsize;
			$fontsize+=5;
			continue;
		}
		//syslog(LOG_EMERG,print_r($point,true));
		$i-=1;
		continue;
	}
	$point[$i]=$fontsize;
	$fontsize+=5;
}
$point[sizeof($text)-1]=$point[sizeof($text)-2];
//文字サイズを調整する
$timestr.="フォントサイズ決定".date("s")."\n";

//syslog(LOG_EMERG,print_r($point,true));
//書き込む
for($i=1;$i<sizeof($text);$i++){
	$timestr.=$i.":".date("s")."\n";

	if($read[$i]=="")continue;
	$currenttext  = $text[$i];
	unset($text[$i]);
	$searched = array_search($currenttext,$text);
	$text[$i]=$currenttext;
	if($searched!=false and abs($poly[$i][0]["x"]-$poly[$searched][0]["x"])<50 and abs($poly[$i][0]["y"]-$poly[$searched][0]["y"])<50 and $i>$searched){			continue;
	}
	$currentdraw = clone $draw;
	$currentdraw->setFontSize($point[$i]);
// 表示する文字列
	$string = $read[$i];
		
// 文字列の幅を取得
	$metrics = $templateImg->queryFontMetrics( $currentdraw, $string );
//座標を指定して描画
	//$draw->annotation( $poly[$i][0]["x"],$poly[$i][0]["y"], $i);
	$currentdraw->annotation(0, $metrics['ascender'], $string);
 
	// テキストの背景画像 画像サイズは小さくて構いません
	$textBase = new Imagick();
	$textBase->newImage(10, 10,'none');
	// 描画後の大きさに合わせて背景を拡大
	$textBase->scaleimage(ceil($metrics['textWidth']), $metrics['textHeight']); 
 
	// テキスト用背景に描画後、その画像に回転を反映
	$textBase->drawimage($currentdraw);
	$textBase->rotateimage('none', ((isset($tan[$i-1])  and $tan[$i-1]>50 and $tan[$i]<50)? $tan[$i-1]:$tan[$i]));
	// 回転後に合成
	if($incline[$i]==false){$poly[$i][0]["y"]-=$metrics["textHeight"];}
	$templateImg->compositeImage($textBase, Imagick::COMPOSITE_DEFAULT,$poly[$i][0]["x"],$poly[$i][0]["y"]);
	//$draw->annotation( $poly[$i][0]["x"],$poly[$i][0]["y"], $string);
	//$past = [$poly[$i][0]["x"]+$metrics["textWidth"],$poly[$i][0]["y"]+$metrics["textHeight"]];//前のブロックの右下
}
//$templateImg->drawImage( $draw );
// 画像へ文字列を合成！
//syslog(LOG_EMERG,print_r($tan,true));
$timestr.="合成終了".date("s")."\n";
//syslog(LOG_EMERG,print_r($timestr,true));

// ファイルとして出力
$res = $templateImg->writeImage($filePath);
//サムネイル
$thumbImg = $templateImg->clone();
$thumbImg->thumbnailImage(200, 200, true);
$thumbImg->writeImage($filePathTh);
// お掃除
$templateImg->destroy();
}
