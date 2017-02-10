<?php
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
//var_dump(command_carousel());
function command_carousel($userinfo){
    include $userinfo["lang"]<2 ? "./TextData.txt" : ($userinfo["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");
    $columns = []; // カルーセル型カラムを5つ追加する配列
    $actions = [];

    $actions_parameter=["BASE?mode=0","BASE?mode=1","BASE?mode=2","BASE?mode=3","MEAN?hanzi=0","BASE?mode=5","BASE?mode=6","BASE?mode=7","OTHERS","USERCONF","CHARCONF","BASE?mode=11"];	//12個
    for($i=0;$i<4;$i++) {
    // カルーセルに付与するボタンを作る
    	for($j=0;$j<3;$j++){
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message[$i*3+$j],$actions_parameter[$i*3+$j],null);
	}
    	$column = new TemplateBuilder\CarouselColumnTemplateBuilder(null, $column_detail[$i], "https://nobuta.xyz/HanziMutualLearningBot/image/".$i.".PNG", $actions);
    	$columns[] = $column;
    }
// カラムの配列を組み合わせてカルーセルを作成する
    $carousel = new TemplateBuilder\CarouselTemplateBuilder($columns);
// カルーセルを追加してメッセージを作る
    for($i=0;$i<12;$i++)$messageforpc.="\n".$i.":".$actions_message[$i];
    $carousel_message = new TemplateMessageBuilder($messageforpc,$carousel);
    return $carousel_message;
}
