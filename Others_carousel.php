<?php
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
//var_dump(command_carousel());
function others_carousel($userinfo){
    if($userinfo["lang"]>1){
    include "./TextData_TW.txt";
    }else{
    include "./TextData.txt";
    }

    $columns = []; // カルーセル型カラムを5つ追加する配列
    $actions = [];
   //モードは12から16を使う
    $actions_parameter_other=["BASE?mode=12","BASE?mode=13","BASE?mode=14","BASE?mode=15","BASE?mode=16"];	//12個
    for($i=0;$i<5;$i++) {
    // カルーセルに付与するボタンを作る
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message_other[$i],$actions_parameter_other[$i],null);
    	$column = new TemplateBuilder\CarouselColumnTemplateBuilder(null, $column_detail_other[$i], "https://nobuta.xyz/HanziMutualLearningBot/image/others".$i.".png", $actions);
    	$columns[] = $column;
    }
// カラムの配列を組み合わせてカルーセルを作成する
    $carousel = new TemplateBuilder\CarouselTemplateBuilder($columns);
// カルーセルを追加してメッセージを作る
    for($i=0;$i<5;$i++)$messageforpc.="\n".($i+12).":".$actions_message_other[$i];
    $carousel_message = new TemplateMessageBuilder($messageforpc,$carousel);
    return $carousel_message;
}
