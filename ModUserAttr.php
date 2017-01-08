<?php
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
#var_dump(command_carousel());
function modUserAttr(){
    //パラメータを順番に羅列
    $title="利用者情報設定";
    $actions=["","","",""];	//15個
    $infotext = "ご自身の情報を教えてください．";
    $label=["簡体中国語学習者","繁体中国語学習者","簡体中国語話者","繁体中国語話者"];
    for($i=0;$i<4;$i++) {
    // アクション（選択肢）を作る
    	$actions[$j] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label[$i], "ALTINFO?lang=".$i);
    }
    $button = new TemplateBuilder\ButtonTemplateBuilder($title,$infotext,"",$actions);
    //ボタンを追加してメッセージを作る
    $button_message = new TemplateMessageBuilder("利用者情報設定，デフォルトでは繁体中国語学習者とします．", $button);
    return $button_message;
}
