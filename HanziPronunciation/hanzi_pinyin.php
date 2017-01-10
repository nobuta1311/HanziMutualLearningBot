#!/usr/bin/php
<?php
include "pinyin_bpmf.php";
mb_internal_encoding("UTF-8");
$hanzionly=false;
$ispinyin =false;
$issum    =false;
if(isset($_GET["str"])){ //URLでやる場合
    $source=$_GET["str"];
}else if($argc>1){	 //引数でやる場合
    if( $argv[2]==1){
        $hanzionly=true;
    }
    if($argv[3]==1){
	$ispinyin=true;
    }
    if($argv[4]==1){
	$issum=true;
    }
    $source = urldecode($argv[1]);
}else{
    $source="亜";
}
//file_put_contents("./log/sourcelog.txt",$source);
//入力を受け取る 漢字+アルファの文字列で渡される
$pastchars = [];
$output="";
for($i=0;$i<mb_strlen($source,"UTF-8");$i++){
    $target=mb_substr($source,$i,1,"UTF-8");
    
    $code= strtoupper(substr(json_encode($target),3,4));
    if(hexdec($code)<hexdec("4E00") || hexdec($code)>hexdec("9FA5")){
        if($hanzionly==false){
		$output.= $target;
        }
        continue;
    }
    //新規漢字ならばリストに追加
    //漢字を表示する
    if(!$issum) $output.=" ";
    if(in_array($target,$pastchars,true)){//含まれる場合
	if($issum){continue;}else{$output.= $target;}
    }else{
	if($issum){$output.= $target;}
        else{ $output.= $target;}
        $pastchars[]=$target;
    }
 

   $filepath=dirname(__FILE__)."/pinyin.csv";
    $file = new SplFileObject($filepath); 
    $file->setFlags(SplFileObject::READ_CSV); 

    foreach ($file as $key => $line){
        //$records[$key] = $line;
        if($line[0]===$code){ //文字コードが適合したら
            $pinyinres= pinyinchar($line[1])." ";//\\n";
	 //   print $pinyinres
	    if($ispinyin==true) $chartransed= $pinyinres;
	    else $chartransed= pinyinToBpmf($line[1]);
	    if($issum==true) $output.=$chartransed."\n";
	    else $output.=$chartransed." ";
            break;
        }
    }

}

function pinyinchar($s){
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
    else if($che!=FALSE){
        $result= mb_substr($s,0,$che).rehan($codes[2][$tension]).mb_substr($s,$che+1); 
    }else if($chu!=FALSE || $chi!=FALSE){
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
    return $result;
}
print json_encode($output);
function rehan($code){
    $res= mb_convert_encoding(pack("H*",str_repeat('0', 8 - strlen($code)).$code), 'UTF-8', 'UTF-32BE');
    return $res;
}
