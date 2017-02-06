<?php
//文字を表す番号を取得
function pinyinToBpmf($input){
$input=mb_strtolower($input);//大文字は処理できないので直す
preg_match("/[1-5]/",$input,$matches,PREG_OFFSET_CAPTURE);//声調数字の場所を変更する
if($matches[0][1]!=strlen($input)-1)$input=substr($input,0,$matches[0][1]).substr($input,$matches[0][1]+1).$matches[0][0];

$voice=array(1=>"",2=>10,3=>7,4=>11,5=>25);
$consonant=array("b"=>0,"p"=>1,"m"=>2,"f"=>3,"d"=>4,"t"=>5,"n"=>6,"l"=>7,"g"=>8,"k"=>9,"h"=>10,"j"=>11,"q"=>12,"x"=>13,"zh"=>14,"zhi"=>14,"ch"=>15,"chi"=>15,"sh"=>16,"shi"=>16,"r"=>17,"ri"=>17,"z"=>18,"zi"=>18,"c"=>19,"ci"=>19,"s"=>20,"si"=>20);
$vowel=array("v"=>15,"u"=>15,"a"=>0,"o"=>1,"e"=>2,"ai"=>4,"ei"=>5,"ao"=>6,"ou"=>7,"an"=>8,"en"=>9,"ang"=>10,"eng"=>11,"er"=>12,"yi"=>13,"i"=>13,"ya"=>[13,0],"ia"=>[13,0],"ye"=>[13,3],"ie"=>[13,3],"yao"=>[13,6],"iao"=>[13,6],"you"=>[13,7],"iu"=>[13,7],"yan"=>[13,8],"ian"=>[13,8],"yin"=>[13,9],"in"=>[13,9],"yang"=>[13,10],"iang"=>[13,10],"ying"=>[13,11],"ing"=>[13,11],"wu"=>14,"u"=>14,"wa"=>[14,0],"ua"=>[14,0],"wo"=>[14,1],"uo"=>[14,1],"wai"=>[14,4],"uai"=>[14,4],"wei"=>[14,5],"ui"=>[14,5],"wan"=>[14,8],"uan"=>[14,8],"wen"=>[14,9],"un"=>[14,9],"wang"=>[14,10],"uang"=>[14,10],"weng"=>[14,11],"ong"=>[14,11],"yu"=>15,"u:"=>15,"yue"=>[15,3],"u:e"=>[15,3],"yuan"=>[15,8],"u:an"=>[15,8],"yun"=>[15,9],"u:n"=>[15,9],"yong"=>[15,11],"iong"=>[15,11],"xu"=>[13-21,15],"qu"=>[12-21,15],"ju"=>[11-21,15]);
	
$length=strlen($input);
$result=array();
for($i=0;$i<$length;$i++){
	$transed=null;
	$isvowel=false;
	$isconso=false;
	$a = $input[$i];
	if($i<$length-1){
		$b= $input[$i+1];
		if($i<$length-2){
			$c = $input[$i+2];
			if($i<$length-3){
				$d = $input[$i+3];
				if(isset($vowel[$a.$b.$c.$d])){
					$transed=$vowel[$a.$b.$c.$d];
					$i+=3;
					$isvowel=true;
				}
			}else{$d="";}
			if($transed===null && isset($vowel[$a.$b.$c])){
				$transed = $vowel[$a.$b.$c];
				$i+=2;
				$isvowel=true;
			}
			if($transed===null &&isset($consonant[$a.$b.$c])){
				$transed = $consonant[$a.$b.$c];
				$isconso =true;
				$i+=2;
			}
		}else{$c="";}
		if($transed===null && isset($vowel[$a.$b])){
			$transed = $vowel[$a.$b];
			$i+=1;
			$isvowel=true;
		}
		if($transed===null &&isset($consonant[$a.$b])){
			$transed = $consonant[$a.$b];
			$isconso =true;
			$i+=1;
		}
	}else{$b="";}
	if($transed===null && isset($vowel[$a])){
		$transed = $vowel[$a];
		$isvowel=true;
	}
	if($transed===null && isset($consonant[$a])){
		$transed = $consonant[$a];
		$isconso = true;
	}
	if($transed===null && isset($voice[$a])){
		$transed = $voice[$a];
	}	//声調
	//ここから文字に直す
	if(is_array($transed)){
		if($isvowel){//母音なら
			$result[$i]=rehan(dechex(hexdec("3105")+21+$transed[0])).rehan(dechex(hexdec(3105)+21+$transed[1]));
		}else{		//子音なら	
			$result[$i]=rehan(dechex(hexdec("3105")+$transed[0])).rehan(dechex(hexdec(3105)+$transed[1]));
		}
	}else{//そのまま
		if($isvowel){//母音なら
			$result[$i]=rehan(dechex(hexdec("3105")+21+$transed));
		}elseif($isconso){		//子音なら	
			$result[$i]=rehan(dechex(hexdec("3105")+$transed));
		}else{	//声調
			if($transed=="")$result[$i]="";
			else $result[$i]=rehan(dechex(hexdec("02C0")+$transed));
		}
	}
}
	return implode("",$result);
}
//print implode("",$result);
//別のファイルにも有る
//コードを文字にする
/*
function rehan($code){
    $res= mb_convert_encoding(pack("H*",str_repeat('0', 8 - strlen($code)).$code), 'UTF-8', 'UTF-32BE');
    return $res;
}
*/
