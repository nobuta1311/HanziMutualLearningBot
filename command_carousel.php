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
    $actions_message=["発音の参照","漢字１文字の参照","意味と発音の参照","クイズを開始","学習状況画像","記録済み漢字一覧","簡体字繁体字相互変換","音声確認","フィードバック","ユーザ設定変更","発音記号種類変更","リセット"];//12個
    $actions_parameter=["BASE?mode=0","BASE?mode=1","BASE?mode=2","BASE?mode=3","BASE?mode=4","BASE?mode=5","BASE?mode=6","BASE?mode=7","USERCONF","USERCONF","USERCONF","USERCONF"];	//12個
    $column_title=["漢字の読み方の参照","学習","サブ機能","設定変更"]; //4個
    $column_detail=["入力された文字列から漢字を認識して参照します","学習のためのツールです","便利なサブ機能です","各種設定を行います"];
    for($i=0;$i<4;$i++) {
    // カルーセルに付与するボタンを作る
    	for($j=0;$j<3;$j++){
    	//$actions[$j] = new TemplateActionBuilder\UriTemplateActionBuilder($actions_message[$i*3+$j], "https://nobuta.xyz");//$actions_uri[$i*3+$j] );
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message[$i*3+$j],$actions_parameter[$i*3+$j]);
	}
    // カルーセルのカラムを作成する
    	$column = new TemplateBuilder\CarouselColumnTemplateBuilder($column_title[$i], $column_detail[$i], "https://nobuta.xyz/HanziMutualLearningBot/image/command".$i.".png", $actions);
    	$columns[] = $column;
    }
// カラムの配列を組み合わせてカルーセルを作成する
    $carousel = new TemplateBuilder\CarouselTemplateBuilder($columns);
// カルーセルを追加してメッセージを作る
    $carousel_message = new TemplateMessageBuilder("モード選択\nデフォルトでは漢字の認識をします．", $carousel);
    return $carousel_message;
}
