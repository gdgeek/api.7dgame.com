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
        'title' => 'XR UGC平台',
        'sub-title' => 'XR UGC Platform',
        'company' => '上海不加班网络科技有限公司',
        'company-url' => 'https://bujiaban.com',
        'aka' => 'XRUGC.com',
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
    'identityAuth' => [
        'AUTH_PROVIDER' => getenv('AUTH_PROVIDER') ?: 'legacy',
        'IDENTITY_AUTH_BASE_URL' => getenv('IDENTITY_AUTH_BASE_URL') ?: null,
        'IDENTITY_AUTH_TIMEOUT_MS' => getenv('IDENTITY_AUTH_TIMEOUT_MS') ?: 1500,
        'IDENTITY_AUTH_CONNECT_TIMEOUT_MS' => getenv('IDENTITY_AUTH_CONNECT_TIMEOUT_MS') ?: 300,
        'IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK' => getenv('IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK') ?: 'true',
        'IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER') ?: 'legacy',
        'IDENTITY_ACCOUNT_LIFECYCLE_ENABLED' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_ENABLED') ?: 'false',
        'IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK') ?: 'true',
        'IDENTITY_ACCOUNT_REGISTER_ENABLED' => getenv('IDENTITY_ACCOUNT_REGISTER_ENABLED') ?: 'false',
        'IDENTITY_ACCOUNT_PASSWORD_ENABLED' => getenv('IDENTITY_ACCOUNT_PASSWORD_ENABLED') ?: 'false',
        'IDENTITY_ACCOUNT_EMAIL_ENABLED' => getenv('IDENTITY_ACCOUNT_EMAIL_ENABLED') ?: 'false',
        'IDENTITY_ACCOUNT_INVITATION_ENABLED' => getenv('IDENTITY_ACCOUNT_INVITATION_ENABLED') ?: 'false',
        'IDENTITY_ACCOUNT_INTERNAL_TOKEN' => getenv('IDENTITY_ACCOUNT_INTERNAL_TOKEN') ?: (getenv('IDENTITY_INTERNAL_API_TOKEN') ?: null),
        'IDENTITY_INTERNAL_API_TOKEN' => getenv('IDENTITY_INTERNAL_API_TOKEN') ?: null,
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
