<?php

use yii\helpers\Html;
$params = [
    'adminEmail' => 'dirui@mrpp.com',
    'supportEmail' => 'dirui@mrpp.com',
    'senderEmail' => 'dirui@mrpp.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
 //   'identicon' => new \Identicon\Identicon(),
    'information' => [
        'title' => 'AR创作平台',
        'sub-title' => 'AR Creation Platform',
        'company' => '上海不加班网络科技有限公司',
        'company-url' => 'https://bujiaban.com',
        'aka' => 'MrPP.com',
        'disk' => false,
        'local' => false,
    ],
    'mdm.admin.configs' => [
        'advanced' => [
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
        ],
    ],
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

if ($title) {
    $params['information']['title'] = Html::encode($title);
}
if ($sub_title) {
    $params['information']['sub-title'] = Html::encode($sub_title);
}
if ($title) {
    $params['information']['company'] = Html::encode($company);
}
if ($company_url) {
    $params['information']['company-url'] = Html::encode($company_url);
}
if ($aka) {
    $params['information']['aka'] = Html::encode($aka);
}
if ($local) {
    $params['information']['local'] = true;
}
if ($ip) {
    $params['information']['ip'] = $ip;
}
if ($disk) {
    $params['information']['disk'] = true;
}

return $params;
