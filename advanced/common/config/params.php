<?php

use yii\helpers\Html;
$params = [
    'adminEmail' => 'dirui@mrpp.com',
    'supportEmail' => 'dirui@mrpp.com',
    'senderEmail' => 'dirui@mrpp.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
	'identicon' =>  new \Identicon\Identicon(),
    'information' => [
        'title'=>'混合现实编程平台',
        'sub-title'=>'Mixed Reality Programming Platform',
        'company' => '上海不加班网络科技有限公司',
        'company-url' => 'https://bujiaban.com',
        'aka'=>'MrPP.com',
        'disk'=>false,
        'local'=>false,
    ],
    'mdm.admin.configs' => [
        'advanced'=> [
          'restful' => [
              '@common/config/main.php',
              '@common/config/main-local.php',
              '@api/config/main.php',
              '@api/config/main-local.php',
          ],
          'manager' => [
              '@common/config/main.php',
              '@common/config/main-local.php',
              '@backend/config/main.php',
              '@backend/config/main-local.php',
          ],
      ]
    ]
];

$title = getenv('MRPP_TITLE');
$sub_title = getenv('MRPP_SUB_TITLE');
$company = getenv('MRPP_COMPANY');
$company_url = getenv('MRPP_COMPANY_URL');
$aka = getenv('MRPP_AKA');
$local = getenv('MRPP_LOCAL');
$ip = getenv('MRPP_IP');
$disk = getenv('MRPP_DISK');
$api = getenv('MRPP_API');
$pub = getenv('MRPP_PUB');

if($title){
    $params['information']['title'] = Html::encode($title);
}
if($sub_title){
    $params['information']['sub-title'] = Html::encode($sub_title);
}
if($title){
    $params['information']['company'] = Html::encode($company);
}
if($company_url){
    $params['information']['company-url'] = Html::encode($company_url);
}
if($aka){
    $params['information']['aka'] = Html::encode($aka);
}
if($local){
    $params['information']['local'] = ($local != 0);
}
if($ip){
    $params['information']['ip'] = $ip;
}
if($disk){
    $params['information']['disk'] = true;
}
if($api){
    $params['information']['api'] = Html::encode($api);
}else{

    $params['information']['api'] = Html::encode('https://api.mrpp.com');
}
if($pub){
    $params['information']['pub'] = Html::encode($pub);
}else{

    $params['information']['pub'] = Html::encode('https://public.mrpp.com');
}

return $params;