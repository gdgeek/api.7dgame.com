<?php

namespace api\modules\v1\services;

use Yii;
use yii\db\Connection;

/** Temporary baseline evidence for unscanned WeChat tickets, without secrets. */
class LegacyWechatSceneRecorder
{
    public static function capture(string $scene, string $appId, int $issuedAt, int $lifetime, string $source): void
    {
        if (!in_array(strtolower(getenv('IDENTITY_CAPTURE_LEGACY_QR_SCENES') ?: 'false'), ['1', 'true', 'yes', 'on'], true)) {
            return;
        }
        self::record(Yii::$app->db, $scene, $appId, $issuedAt, $issuedAt + $lifetime, $source);
    }

    public static function record(Connection $db, string $scene, string $appId, int $issuedAt, int $expiresAt, string $source): void
    {
        if (IndependentIdentity::enabled()) {
            throw new \LogicException('Legacy QR scene capture cannot run after Identity activation.');
        }
        if ($scene === '' || strlen($scene) > 255 || trim($appId) === '' || strlen($appId) > 128
            || $issuedAt <= 0 || $expiresAt <= $issuedAt || $expiresAt - $issuedAt > 6 * 24 * 3600
            || !in_array($source, ['api/wechat/qrcode', 'api/site/wechat-qrcode', 'v1/wechat/qrcode'], true)) {
            throw new \InvalidArgumentException('Invalid legacy QR evidence.');
        }
        $db->createCommand()->insert('{{%identity_legacy_wechat_scenes}}', [
            'scene_hash' => hash('sha256', $scene), 'provider_app_id' => $appId,
            'issued_at' => $issuedAt, 'expires_at' => $expiresAt, 'source' => $source,
        ])->execute();
    }
}
