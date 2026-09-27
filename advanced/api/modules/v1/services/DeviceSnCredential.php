<?php

namespace api\modules\v1\services;

use yii\web\BadRequestHttpException;
use common\components\security\ServiceUnavailableHttpException;

/** Random bearer credentials. Only digests and authenticated ciphertext enter storage. */
final class DeviceSnCredential
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    private ?array $configuredKeys;
    private ?string $configuredActiveKey;

    public function __construct(?array $keys = null, ?string $activeKey = null)
    {
        $this->configuredKeys = $keys;
        $this->configuredActiveKey = $activeKey;
    }

    public static function normalize(string $value): string
    {
        if (strlen($value) > 128) {
            throw new BadRequestHttpException('Invalid SN format.');
        }
        $value = strtoupper(preg_replace('/[\s-]+/', '', $value));
        if (!preg_match('/^[0-9A-HJKMNP-TV-Z]{32}$/D', $value)) {
            throw new BadRequestHttpException('Invalid SN format.');
        }
        return $value;
    }

    public static function digest(string $value): string
    {
        return hash('sha256', self::normalize($value));
    }

    public static function format(string $value): string
    {
        return implode('-', str_split(self::normalize($value), 4));
    }

    public function generate(): array
    {
        $sn = '';
        foreach (unpack('C*', random_bytes(32)) as $byte) {
            $sn .= self::ALPHABET[$byte & 31];
        }
        $hash = self::digest($sn);
        [$keys, $active] = $this->keyring();
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($sn, 'aes-256-gcm', $keys[$active], OPENSSL_RAW_DATA,
            $nonce, $tag, $this->aad($active, $hash), 16);
        if ($ciphertext === false) {
            throw new ServiceUnavailableHttpException('SN encryption is unavailable.');
        }
        return [
            'sn' => self::format($sn),
            'sn_hash' => $hash,
            'sn_tail' => substr($sn, -4),
            'sn_ciphertext' => base64_encode($nonce . $tag . $ciphertext),
            'key_id' => $active,
        ];
    }

    public function decrypt(string $ciphertext, string $keyId, string $hash): string
    {
        [$keys] = $this->keyring();
        $bytes = base64_decode($ciphertext, true);
        if (!isset($keys[$keyId]) || $bytes === false || strlen($bytes) !== 60) {
            throw new ServiceUnavailableHttpException('SN decryption is unavailable.');
        }
        $sn = openssl_decrypt(substr($bytes, 28), 'aes-256-gcm', $keys[$keyId], OPENSSL_RAW_DATA,
            substr($bytes, 0, 12), substr($bytes, 12, 16), $this->aad($keyId, $hash));
        if (!is_string($sn) || !hash_equals($hash, hash('sha256', $sn))) {
            throw new ServiceUnavailableHttpException('SN decryption is unavailable.');
        }
        return self::format($sn);
    }

    private function keyring(): array
    {
        $encoded = $this->configuredKeys;
        if ($encoded === null) {
            $encoded = json_decode(getenv('DEVICE_SN_KEYS') ?: '', true);
        }
        $active = $this->configuredActiveKey ?? (getenv('DEVICE_SN_ACTIVE_KEY_ID') ?: '');
        if (!is_array($encoded) || !$encoded || !is_string($active) || !isset($encoded[$active])) {
            throw new ServiceUnavailableHttpException('SN encryption keys are not configured.');
        }
        $keys = [];
        foreach ($encoded as $id => $value) {
            $key = is_string($value) ? base64_decode($value, true) : false;
            if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', (string)$id) || $key === false || strlen($key) !== 32) {
                throw new ServiceUnavailableHttpException('SN encryption keys are invalid.');
            }
            $keys[(string)$id] = $key;
        }
        return [$keys, $active];
    }

    private function aad(string $keyId, string $hash): string
    {
        return 'device-sn:v1:' . $keyId . ':' . $hash;
    }
}
