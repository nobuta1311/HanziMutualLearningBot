<?php
mb_internal_encoding("utf-8");
//print strHanziRead("蔡英文",true,1,true,true);
function strHanziRead($inputstr,$hanzionly=false,$userinfo,$issum=false,$mean=false,$readonly=false){
for($i=0;$i<mb_strlen(strHanziOnly($inputstr));$i++){
	loggingLearntHanzi($userinfo,mb_substr(strHanziOnly($inputstr),$i,1),0);
}

include "./pinyin_bpmf.php";
//meanはtrueならばissumもhanzionlyもtrueである
if($mean){$hanzionly=true;$issum=true;}
$hanzicount=0;
if($userinfo["char"]%2==0)$ispinyin=true;
else $ispinyin=false;
//hanzionlyならば関係ない文字を出力しない

//syslog(LOG_EMERG,print_r($inputstr,true));
$inputstrlen=mb_strlen($inputstr);
$outputread=array();
$outputmean=array();
$outputstr="";
$outputread="";
for($i=10;$i>1;$i--){
	if($inputstrlen<$i)continue;
	for($j=0;$j+$i<=$inputstrlen;$j++){
		$finded = searchFromDictEach(mb_substr($inputstr,$j,$i));
		if(sizeof($finded)>0){
		$hanzicount+=$i;

	//	for($k=0;$k<$i;$k++){$outputarr[$j+$k]=mb_substr($inputstr,$j+$k,1);}
		$outputchar[$j]=$finded[0]["trad_str"];
		$outputread[$j]="";
		$outputmean[$j]=$finded[0]["eng_mean"];
		$readalpha=$finded[0]["read_str"];
		
		foreach(explode(" ",$readalpha) as $key=>$val){
			//syslog(LOG_EMERG,print_r($val,true));

			if($ispinyin)
				$outputread[$j].=pinyinChar($val)." ";
			else
				$outputread[$j].=pinyinToBpmf($val)." ";
		}


		$replacestr="";
		for($k=0;$k<$i;$k++){$replacestr.="#";}
		$inputstr=preg_replace("/".mb_substr($inputstr,$j,$i)."/u",$replacestr,$inputstr,1);
		}
	}
}



for($j=0;$j<$inputstrlen;$j++){	//最後の１文字は別の辞書から
	$acode= strtoupper(substr(json_encode(mb_substr($inputstr,$j,1)),3,4));
	if(mb_substr($inputstr,$j,1)!="#" and (hexdec($acode)<hexdec("4E00") || hexdec($acode)>hexdec("9FA5"))){//漢字でない場合
        	if(!$hanzionly){
			$outputchar[$j]= mb_substr($inputstr,$j,1);
			$outputread[$j]="";
        	}
       		//continue;
    	}elseif(mb_substr($inputstr,$j,1)!="#"){
		$hanzicount+=1;
		$searchedPinyin=searchFromReading(mb_substr($inputstr,$j,1),null,$userinfo);
		$outputchar[$j]=mb_substr($inputstr,$j,1);


		if($ispinyin)$outputread[$j]=$searchedPinyin." ";
		else $outputread[$j]=pinyinToBpmf(charPinyin($searchedPinyin))." ";
		//syslog(LOG_EMERG,print_r(charPinyin($searchedPinyin),true));
	}else{
	}
}

for($i=0;$i<$inputstrlen;$i++){
	if(isset($outputread[$i])){
		if($issum and mb_strstr($outputstr,$outputchar[$i])!==false){
			continue;
		}
		$outputstr.=$outputchar[$i];
		if($issum){
			$outputstr.=":".$outputread[$i];
			$outputonlyread.=$outputread[$i];
			if($mean and isset($outputmean[$i])) {
				$outputstr.="\n".$outputmean[$i];
			}
		}else{
			$outputstr.=$outputread[$i];
		}
		if($hanzionly)$outputstr.="\n";
		//syslog(LOG_EMERG,print_r($outputchar,true));
		//syslog(LOG_EMERG,print_r($outputread,true));
	}
}
if($hanzicount==0)$outputstr="";
if($readonly==true) return $outputonlyread;
return rtrim($outputstr,"\n");
}

function searchFromDictEach($word){
include "/var/www/html/HanziMutualLearningBot/HanziPronunciation/idpass.php";
$query = "SELECT * FROM dict where trad_str=\"".$word."\" or simp_str=\"".$word."\"";
$result =sendQuery($query);
return $result;
}

function searchFromReading($inputchar,$inputcharcode,$userinfo){//単一文字とする 
include "/var/www/html/HanziMutualLearningBot/HanziPronunciation/idpass.php";
$query="";
if($userinfo["lang"]%2==0){//簡体字
	if($inputchar==null)
		$query="select mandarin_cn from reading where char_code=\"".$inputcharcode."\"";
	else
		$query="select mandarin_cn from reading where achar=\"".$inputchar."\"";
	$result = sendQuery($query);
	if(isset($result[0]) and isset($result[0]["mandarin_cn"]))
		return $result[0]["mandarin_cn"];
	else
		return null;
}
else{			//繁体字のとき
	if($inputchar==null)
		$query="select mandarin_tw from reading where char_code=\"".$inputcharcode."\"";
	else
		$query="select mandarin_tw from reading where achar=\"".$inputchar."\"";
	$result = sendQuery($query);
	if(isset($result[0]) and isset($result[0]["mandarin_tw"]))
		return $result[0]["mandarin_tw"];
	else
		return null;
}
}

function charPinyin($s){
    $returnstr="";
    for($k=0;$k<mb_strlen($s);$k++){
    $target=mb_substr($s,$k,1);
    if(ctype_alnum($target)){$returnstr.=$target;continue;}
    $acode= strtoupper(substr(json_encode($target),3,4));
    $codes=array(
        array( //a
            "0101","00E1","01CE","00E0","0061"
        ),
        array( //o
            "014D","00F3","01D2","00F2","006F"
        ),
        array(//e
            "0113","00E9","011B","00E8","0065"
        ),
        array(//u
            "016B","00FA","01D4","00F9","0075"
        ),
        array(//i
            "012B","00ED","01D0","00EC","0069"
        ),
        array(//v
            "01D6","01D8","01DA","01DC","00FC"
        )
    );
    $codesbase="aoeuiv";$codesbasevoice="12345";
    for($i=0;$i<6;$i++){
	for($j=0;$j<5;$j++){
	    if($codes[$i][$j]===$acode){
		$returnstr.= $codesbase[$i].$codesbasevoice[$j];
	    	//break;
	    }
	}
    }
    }
    return $returnstr;
}

function pinyinChar($s){
    $tension=substr($s,-1)-1;
    $s=substr($s,0,strlen($s)-1);

    $codes=array(
        array( //a
            "0101","00E1","01CE","00E0","0061"
        ),
        array( //o
            "014D","00F3","01D2","00F2","006F"
        ),
        array(//e
            "0113","00E9","011B","00E8","0065"
        ),
        array(//u
            "016B","00FA","01D4","00F9","0075"
        ),
        array(//i
            "012B","00ED","01D0","00EC","0069"
        ),
        array(//v
            "01D6","01D8","01DA","01DC","00FC"
        )
    );
       //var_dump($codes);
    $cha=strpos($s,"a");
    $cho=strpos($s,"o");
    $che=strpos($s,"e");
    $chu=strrpos($s,"u");
    $chi=strrpos($s,"i");
    if($cha!==FALSE){
        $result= mb_substr($s,0,$cha).rehan($codes[0][$tension]).mb_substr($s,$cha+1);
    }else if($cho!==FALSE){
        $result= mb_substr($s,0,$cho).rehan($codes[1][$tension]).mb_substr($s,$cho+1);
    }
    else if($che!==FALSE){
	//syslog(LOG_EMERG,print_r(mb_substr($s,0,$che)."~".rehan($codes[2][$tension])."~".mb_substr($s,$che+1),true));
	
        $result= mb_substr($s,0,$che).rehan($codes[2][$tension]).mb_substr($s,$che+1); 
    }else if($chu!==FALSE || $chi!==FALSE){
        if($chu<$chi){
            $result= mb_substr($s,0,$chi).rehan($codes[4][$tension]).mb_substr($s,$chi+1); 
        }else{
            //u:の発音を考慮
            if(mb_substr($s,$chi+2,1)==":"){
                $result= mb_substr($s,0,$chu).rehan($codes[5][$tension]).mb_substr($s,$chu+3);
            }else{
                $result= mb_substr($s,0,$chu).rehan($codes[3][$tension]).mb_substr($s,$chu+1); 
            }
        }
    }else{
        $result= $s;
    }
    $chv=strrpos($s,":"); //声調のつかないu:を考慮
    if($chv!==FALSE){
        $result=mb_substr($result,0,$chv-1).rehan($codes[5][4]).mb_substr($result,$chv+1);
    }
    //syslog(LOG_EMERG,print_r($result,true));

    return $result;
}

function rehan($code){
    $res= mb_convert_encoding(pack("H*",str_repeat('0', 8 - strlen(mb_strtolower($code))).$code), 'UTF-8', 'UTF-32BE');
    return $res;
}
function strHanziOnly($inputstr){
	$inputstrlen = mb_strlen($inputstr);
	$outputstr.="";
	for($j=0;$j<$inputstrlen;$j++){	//最後の１文字は別の辞書から
	$acode= strtoupper(substr(json_encode(mb_substr($inputstr,$j,1)),3,4));
	if(mb_substr($inputstr,$j,1)!="#" and (hexdec($acode)<hexdec("4E00") || hexdec($acode)>hexdec("9FA5"))){//漢字でない場合
        }else{
		$outputstr.=mb_substr($inputstr,$j,1);
	}
	}
	return $outputstr;
}

