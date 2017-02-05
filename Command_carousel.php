<?php
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
//var_dump(command_carousel());
function selectCharTypeButton(){
	
}
function command_carousel(){
    $columns = []; // カルーセル型カラムを5つ追加する配列
    $actions = [];
    //パラメータを順番に羅列
    $actions_message=["発音の参照","漢字１文字の参照","意味と発音の参照","発音確認問題","語彙選択問題","学習状況参照","簡体字繁体字相互変換","音声確認","他言語切り替え","ユーザ設定変更","発音記号種類変更","フィードバック"];//12個
    $actions_parameter=["BASE?mode=0","BASE?mode=1","BASE?mode=2","BASE?mode=3","BASE?mode=4","BASE?mode=5","BASE?mode=6","BASE?mode=7","OTHERS","USERCONF","CHARCONF","BASE?mode=11"];	//12個
    $column_detail=["発音を参照します","発音・語彙の問題を出します","各種機能に切り替えます","各種設定を行います"];
    for($i=0;$i<4;$i++) {
    // カルーセルに付与するボタンを作る
    	for($j=0;$j<3;$j++){
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message[$i*3+$j],$actions_parameter[$i*3+$j]);
	}
    	$column = new TemplateBuilder\CarouselColumnTemplateBuilder(null, $column_detail[$i], "https://nobuta.xyz/HanziMutualLearningBot/image/command".$i.".png", $actions);
    	$columns[] = $column;
    }
// カラムの配列を組み合わせてカルーセルを作成する
    $carousel = new TemplateBuilder\CarouselTemplateBuilder($columns);
// カルーセルを追加してメッセージを作る
    $messageforpc = "モード選択\n@@@mode=1 などと入力すると返信を変更できます．";
    for($i=0;$i<12;$i++)$messageforpc.="\n".$i.":".$actions_message[$i];
    $carousel_message = new TemplateMessageBuilder($messageforpc,$carousel);
    return $carousel_message;
}
