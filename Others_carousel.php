<?php
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
//var_dump(command_carousel());
function others_carousel(){
    $columns = []; // カルーセル型カラムを5つ追加する配列
    $actions = [];
    //パラメータを順番に羅列
    $actions_message=["広東語に変更","朝鮮語に変更","日本語かなに変更","日本語発音に変更","ベトナム語に変更"];//12個
    //モードは12から16を使う
    $actions_parameter=["BASE?mode=12","BASE?mode=13","BASE?mode=14","BASE?mode=15","BASE?mode=16"];	//12個
    $column_detail=["広東語発音を参照します","朝鮮語を参照します","日本語をひらがなにします","日本語発音を参照します","ベトナム語発音を参照します"];
    for($i=0;$i<5;$i++) {
    // カルーセルに付与するボタンを作る
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message[$i],$actions_parameter[$i],null);
    	$column = new TemplateBuilder\CarouselColumnTemplateBuilder(null, $column_detail[$i], "https://nobuta.xyz/HanziMutualLearningBot/image/others".$i.".png", $actions);
    	$columns[] = $column;
    }
// カラムの配列を組み合わせてカルーセルを作成する
    $carousel = new TemplateBuilder\CarouselTemplateBuilder($columns);
// カルーセルを追加してメッセージを作る
    $messageforpc = "他言語モード選択\n@@@mode=1 などと入力すると返信を変更できます．";
    for($i=0;$i<5;$i++)$messageforpc.="\n".($i+12).":".$actions_message[$i];
    $carousel_message = new TemplateMessageBuilder($messageforpc,$carousel);
    return $carousel_message;
}
