<?php

namespace common\models;

use api\modules\v1\services\IdentityProviderClient;
use common\components\security\ServiceUnavailableHttpException;
use Yii;

/** Identity mutates; PHP retains the existing mail templates and page models. */
class IdentityLegacyFormBridge
{
    public function operation(string $operation, array $payload): array
    {
        $client = Yii::createObject(IdentityProviderClient::class);
        $result = $client->legacyEmailLink($operation, $payload, [
            'idempotency_key' => Yii::$app->request->headers->get('Idempotency-Key'),
        ]);
        return is_array($result['data'] ?? null) ? $result['data'] : $result;
    }

    public function sendEmail(string $operation, array $payload): bool
    {
        $data = $this->operation($operation, $payload);
        $profile = is_array($data['user'] ?? null) ? $data['user'] : $data;
        if (!is_string($profile['username'] ?? null) || !is_string($profile['email'] ?? null)) {
            throw new ServiceUnavailableHttpException('Identity returned an invalid mail recipient.');
        }
        // An in-memory DTO only; none of these values are saved to the business DB.
        $user = (object)[
            'username' => $profile['username'], 'email' => $profile['email'],
            'password_reset_token' => $operation === 'request-reset' ? ($data['token'] ?? null) : null,
            'verification_token' => $operation !== 'request-reset' ? ($data['token'] ?? null) : null,
        ];
        $reset = $operation === 'request-reset';
        $token = $reset ? $user->password_reset_token : $user->verification_token;
        if ($operation === 'signup' && $token === null && ($data['status'] ?? null) === 10) {
            return true;
        }
        if (!is_string($token) || $token === '') {
            throw new ServiceUnavailableHttpException('Identity returned no mail link token.');
        }
        return Yii::$app->mailer->compose($reset
            ? ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text']
            : ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ['user' => $user])
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($user->email)
            ->setSubject($reset ? '重置 ' . Yii::$app->name . ' 上的密码' : '在 ' . Yii::$app->name . ' 上注册新的账户')
            ->send();
    }
}
