<?php
/**
 * 测试邮箱验证 API
 * 
 * 使用方法：
 * php test-email-api.php
 */

// API 基础 URL（根据你的环境修改）
$baseUrl = 'http://localhost:81';

// 测试数据
$testEmail = 'test@example.com';

echo "=== 邮箱验证 API 测试 ===\n\n";

// 测试 1: 发送验证码
echo "测试 1: 发送验证码\n";
echo "URL: {$baseUrl}/v1/email/send-verification\n";
echo "请求数据: " . json_encode(['email' => $testEmail]) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/v1/email/send-verification");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $testEmail]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP 状态码: {$httpCode}\n";
if ($error) {
    echo "cURL 错误: {$error}\n";
}
echo "响应内容:\n";
echo $response . "\n";
echo "\n";

// 解析响应
$responseData = json_decode($response, true);
if ($responseData) {
    echo "解析后的响应:\n";
    print_r($responseData);
} else {
    echo "无法解析 JSON 响应\n";
}

echo "\n=== 测试完成 ===\n";
