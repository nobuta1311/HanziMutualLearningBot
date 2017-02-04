<?php
require_once __DIR__ . '/../line/vendor/autoload.php';
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
function modUserAttr(){
    //パラメータを順番に羅列
    $title="利用者情報設定";
    $infotext = "ご自身の情報を教えてください．";
    $label=["簡体中国語学習者","繁体中国語学習者","簡体中国語話者","繁体中国語話者"];
    for($i=0;$i<4;$i++) {// アクション（選択肢）を作る
    	$actions[$i] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$i], "ALTINFO?lang=".$i);
    }
    $button = new TemplateBuilder\ButtonTemplateBuilder($title,$infotext,null,$actions);//ボタンを追加してメッセージを作る
    $button_message = new TemplateMessageBuilder("利用者情報設定，デフォルトでは繁体中国語学習者とします．", $button);
    return $button_message;
}

function modCharAttr(){
    //パラメータを順番に羅列
    $title="発音符号設定";
    $infotext = "表示してほしい符号を選択してください";
    $label=["拼音","注音"];
    for($i=0;$i<2;$i++) {// アクション（選択肢）を作る
    	$actions[$i] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$i], "ALTCHAR?char=".$i);
    }
    $button = new TemplateBuilder\ButtonTemplateBuilder($title,$infotext,null,$actions);//ボタンを追加してメッセージを作る
    $button_message = new TemplateMessageBuilder("表示用符号選択", $button);
    return $button_message;
}
