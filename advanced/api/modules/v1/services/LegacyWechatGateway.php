<?php

namespace api\modules\v1\services;

use common\components\security\ServiceUnavailableHttpException;

/** Preserve the executable unversioned QR routes while Identity owns all state. */
class LegacyWechatGateway
{
    public function __construct(private IdentityProviderClient $client) {}

    public function forward(string $route, array $body, array $query, array $context): array
    {
        $qr = in_array($route, ['wechat/qrcode', 'site/wechat-qrcode'], true);
        $poll = in_array($route, ['wechat/openid', 'site/wechat-openid'], true);
        $path = $qr ? '/v1/wechat/qrcode' : ($poll ? '/v1/wechat/refresh'
            : ($route === 'wechat/signup' ? '/v1/wechat/register' : '/v1/wechat/binding'));
        $result = $this->client->proxyAccountLifecycle($qr || $poll ? 'GET' : 'POST', $path,
            $qr || $poll ? null : $body, $poll ? ['token' => $query['token'] ?? null] : [], $context);
        if ($result['status'] < 200 || $result['status'] >= 300) {
            return $result;
        }
        $data = $result['body'];
        if ($qr) {
            $ticket = $data['qrcode']['ticket'] ?? null;
            $url = $data['qrcode']['url'] ?? null;
            if (!is_string($ticket) || !is_string($url) || !is_string($data['token'] ?? null)) {
                throw new ServiceUnavailableHttpException('Identity returned an invalid WeChat QR.');
            }
            $result['body'] = ['qrcode' => $route === 'wechat/qrcode' ? $ticket : $url, 'token' => $data['token']];
            if ($route === 'wechat/qrcode') {
                $result['body']['lifetime'] = $data['lifetime'];
            }
            return $result;
        }
        if (!$poll) {
            // Binding already returns the historical shape.
            if ($route === 'wechat/signup') {
                $result['body'] = ['access_token' => $this->accessToken($data), 'code' => 20000];
            }
            return $result;
        }
        if (($data['success'] ?? false) !== true) {
            // This was an empty object in /wechat and a polling code in /site.
            $result['body'] = $route === 'wechat/openid' ? new \stdClass() : ['code' => 20000];
            return $result;
        }
        if (($data['message'] ?? null) === 'signup') {
            $result['body'] = ['token' => $data['token']];
            return $result;
        }
        if (($data['message'] ?? null) !== 'signin') {
            throw new ServiceUnavailableHttpException('Identity returned an invalid WeChat status.');
        }
        $issued = $this->client->proxyAccountLifecycle('POST', '/v1/wechat/login',
            ['token' => $query['token'] ?? null], [], $context);
        if ($issued['status'] < 200 || $issued['status'] >= 300) {
            return $issued;
        }
        $access = $this->accessToken($issued['body']);
        $subject = $this->client->verifyIdentityAccessToken($access);
        $result['body'] = ['token' => $query['token'], 'user' => [
            'nickname' => $subject['nickname'] ?? null, 'username' => $subject['username'], 'roles' => $subject['roles'],
        ], 'access_token' => $access];
        return $result;
    }

    private function accessToken(array $body): string
    {
        $token = $body['token']['accessToken'] ?? $body['token']['token'] ?? $body['access_token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new ServiceUnavailableHttpException('Identity returned no WeChat access token.');
        }
        return $token;
    }
}
