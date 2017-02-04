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
    $actions_message=["発音の参照","漢字１文字の参照","意味と発音の参照","発音確認問題","語彙選択問題","学習状況参照","簡体字繁体字相互変換","音声確認","フィードバック","ユーザ設定変更","発音記号種類変更","学習モードの変更"];//12個
    $actions_parameter=["BASE?mode=0","BASE?mode=1","BASE?mode=2","BASE?mode=3","BASE?mode=4","BASE?mode=5","BASE?mode=6","BASE?mode=7","BASE?mode=8","USERCONF","CHARCONF","USERCONF"];	//12個
    $column_title=["漢字の発音符号の参照","学習教材","付属機能","設定変更"]; //4個
    $column_detail=["入力された文字列から漢字を認識して発音を参照します．\nテキスト/位置情報/画像","発音・語彙の問題を出します\n発音は過去参照した漢字から参照・押下時に出題します．","簡体字⇔繁体字の変換\n漢字の発音音声の生成\n開発者メッセージ送信","各種設定を行います"];
    for($i=0;$i<4;$i++) {
    // カルーセルに付与するボタンを作る
    	for($j=0;$j<3;$j++){
    	//$actions[$j] = new TemplateActionBuilder\UriTemplateActionBuilder($actions_message[$i*3+$j], "https://nobuta.xyz");//$actions_uri[$i*3+$j] );
	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($actions_message[$i*3+$j],$actions_parameter[$i*3+$j]);
	}
    // カルーセルのカラムを作成する $column_title[$i]
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
