<?php

namespace tests\unit\services;

use api\modules\v1\services\DeviceSnCredential;
use common\components\security\LogFilter;
use PHPUnit\Framework\TestCase;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

final class DeviceSnCredentialTest extends TestCase
{
    public function testRoundTripNormalizationAndRandomness(): void
    {
        $codec = new DeviceSnCredential(['v1' => base64_encode(random_bytes(32))], 'v1');
        $seen = [];
        for ($i = 0; $i < 100; $i++) {
            $value = $codec->generate();
            self::assertSame(39, strlen($value['sn']));
            self::assertSame($value['sn_hash'], DeviceSnCredential::digest(strtolower($value['sn'])));
            self::assertSame($value['sn'], $codec->decrypt($value['sn_ciphertext'], 'v1', $value['sn_hash']));
            self::assertStringNotContainsString(DeviceSnCredential::normalize($value['sn']), $value['sn_ciphertext']);
            $seen[$value['sn_hash']] = true;
        }
        self::assertCount(100, $seen);
    }

    public function testRejectsAmbiguousOrOversizedInput(): void
    {
        foreach (['', str_repeat('I', 32), str_repeat('0', 33), str_repeat('0', 200), ['bad']] as $input) {
            try {
                DeviceSnCredential::normalize(is_array($input) ? json_encode($input) : $input);
                self::fail('Invalid SN accepted.');
            } catch (BadRequestHttpException $exception) {
                self::assertSame(400, $exception->statusCode);
            }
        }
    }

    public function testAuthenticatedEncryptionRejectsTamperingAndRecordSwaps(): void
    {
        $codec = new DeviceSnCredential(['v1' => base64_encode(random_bytes(32))], 'v1');
        $value = $codec->generate();
        foreach (['cipher', 'hash', 'key'] as $tamper) {
            $row = $value;
            if ($tamper === 'cipher') {
                $bytes = base64_decode($row['sn_ciphertext']);
                $bytes[30] = chr(ord($bytes[30]) ^ 1);
                $row['sn_ciphertext'] = base64_encode($bytes);
            } elseif ($tamper === 'hash') {
                $row['sn_hash'] = str_repeat('0', 64);
            } else {
                $row['key_id'] = 'missing';
            }
            try {
                $codec->decrypt($row['sn_ciphertext'], $row['key_id'], $row['sn_hash']);
                self::fail('Tampered SN accepted.');
            } catch (HttpException $exception) {
                self::assertSame(503, $exception->statusCode);
            }
        }
    }

    public function testKeyRotationRetainsOldCiphertext(): void
    {
        $keys = ['old' => base64_encode(random_bytes(32)), 'new' => base64_encode(random_bytes(32))];
        $old = (new DeviceSnCredential($keys, 'old'))->generate();
        $rotated = new DeviceSnCredential($keys, 'new');
        self::assertSame($old['sn'], $rotated->decrypt($old['sn_ciphertext'], 'old', $old['sn_hash']));
        self::assertSame('new', $rotated->generate()['key_id']);
    }

    public function testSnIsRedactedWithoutHidingRecordIds(): void
    {
        $sn = str_repeat('A', 32);
        self::assertStringNotContainsString($sn, LogFilter::filter('{"sn":"' . $sn . '"}'));
        self::assertStringNotContainsString($sn, LogFilter::filter('sn=' . $sn));
        $safe = LogFilter::filterArray(['sn' => $sn, 'device_sn_id' => 3, 'sn_tail' => 'AAAA']);
        self::assertSame('[FILTERED]', $safe['sn']);
        self::assertSame(3, $safe['device_sn_id']);
        self::assertSame('AAAA', $safe['sn_tail']);
    }
}
