<?php
require_once __DIR__ . '/../line/vendor/autoload.php';
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder as TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder as TemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder as TemplateActionBuilder;
function modUserAttr($userinfo){ 
    include $userinfo["lang"]<2 ? "./TextData.txt" : ($userinfo["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");

    for($i=0;$i<4;$i++) {// アクション（選択肢）を作る
    	$actions[$i] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label_9[$i], "ALTINFO?lang=".$i);
    }
    $button = new TemplateBuilder\ButtonTemplateBuilder($title_9,$infotext_9,null,$actions);//ボタンを追加してメッセージを作る
    $button_message = new TemplateMessageBuilder($infotext_pc_9,$button);
    return $button_message;
}

function modCharAttr($userinfo){
    include $userinfo["lang"]<2 ? "./TextData.txt" : ($userinfo["lang"]==2 ? "./TextData_CN.txt" : "./TextData_TW.txt");
    for($i=0;$i<2;$i++) {// アクション（選択肢）を作る
    	$actions[$i] = new TemplateActionBuilder\PostbackTemplateActionBuilder($label_10[$i], "ALTCHAR?char=".$i);
    }
    $button = new TemplateBuilder\ButtonTemplateBuilder($title_10,$infotext_10,null,$actions);//ボタンを追加してメッセージを作る
    $button_message = new TemplateMessageBuilder($infotext_pc_10, $button);
    return $button_message;
}
