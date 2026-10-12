<?php

namespace tests\unit\services;

use api\modules\v1\services\DeviceSnCredential;
use common\components\security\LogFilter;
use PHPUnit\Framework\Attributes\DataProvider;
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
            self::assertSame(19, strlen($value['sn']));
            self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{4}(?:-[0-9A-HJKMNP-TV-Z]{4}){3}$/D', $value['sn']);
            self::assertSame(16, strlen(DeviceSnCredential::normalize($value['sn'])));
            self::assertSame(44, strlen(base64_decode($value['sn_ciphertext'], true)));
            self::assertSame(substr($value['sn'], -4), $value['sn_tail']);
            self::assertSame($value['sn_hash'], DeviceSnCredential::digest(" \t" . strtolower($value['sn']) . "\n"));
            self::assertSame($value['sn'], $codec->decrypt($value['sn_ciphertext'], 'v1', $value['sn_hash']));
            self::assertStringNotContainsString(DeviceSnCredential::normalize($value['sn']), $value['sn_ciphertext']);
            $seen[$value['sn_hash']] = true;
        }
        self::assertCount(100, $seen);
    }

    public function testHistorical32CharacterCiphertextAndDigestRemainUnchanged(): void
    {
        $fixture = $this->legacyFixture();
        $row = $fixture['row'];
        $codec = new DeviceSnCredential(['legacy' => $fixture['key']], 'legacy');
        self::assertSame(32, strlen(DeviceSnCredential::normalize($row['sn'])));
        self::assertSame(39, strlen($row['sn']));
        self::assertSame(60, strlen(base64_decode($row['sn_ciphertext'], true)));
        self::assertSame($row['sn_hash'], DeviceSnCredential::digest(strtolower(str_replace('-', ' ', $row['sn']))));
        self::assertSame($row['sn'], $codec->decrypt($row['sn_ciphertext'], 'legacy', $row['sn_hash']));
        self::assertSame(19, strlen($codec->generate()['sn']), 'Keeping a historical key must not generate long SNs.');
    }

    #[DataProvider('invalidInputs')]
    public function testRejectsEveryOtherLengthAndInvalidAlphabet(string $input): void
    {
        $this->expectException(BadRequestHttpException::class);
        DeviceSnCredential::normalize($input);
    }

    public static function invalidInputs(): iterable
    {
        foreach ([0, 1, 8, 15, 17, 20, 24, 31, 33, 48, 64, 129, 200] as $length) {
            yield 'length-' . $length => [str_repeat('A', $length)];
        }
        foreach ([16, 32] as $length) {
            foreach (['I', 'L', 'O', 'U', '/', '_', '字'] as $character) {
                yield 'alphabet-' . $length . '-' . $character => [str_repeat('A', $length - 1) . $character];
            }
        }
        yield 'oversized-before-normalization' => [str_repeat(' ', 128) . str_repeat('A', 16)];
        yield 'json-array' => ['["bad"]'];
    }

    public function testBothFormatsRejectNonceTagCiphertextHashAndKeyTampering(): void
    {
        $fixture = $this->legacyFixture();
        // An alias with identical key bytes must still fail the key-ID AAD check.
        $codec = new DeviceSnCredential(['legacy' => $fixture['key'], 'alias' => $fixture['key']], 'legacy');
        foreach ([$fixture['row'], $codec->generate()] as $value) {
            foreach ([0, 12, 28] as $offset) {
                $bytes = base64_decode($value['sn_ciphertext'], true);
                $bytes[$offset] = chr(ord($bytes[$offset]) ^ 1);
                $this->assertUnavailable(fn() => $codec->decrypt(base64_encode($bytes), $value['key_id'], $value['sn_hash']));
            }
            $this->assertUnavailable(fn() => $codec->decrypt($value['sn_ciphertext'], $value['key_id'], str_repeat('0', 64)));
            $this->assertUnavailable(fn() => $codec->decrypt($value['sn_ciphertext'], 'missing', $value['sn_hash']));
            $this->assertUnavailable(fn() => $codec->decrypt($value['sn_ciphertext'], 'alias', $value['sn_hash']));
        }
    }

    public function testRecordSwapsBetweenOldAndNewCredentialsRemainRejected(): void
    {
        $fixture = $this->legacyFixture();
        $codec = new DeviceSnCredential(['legacy' => $fixture['key']], 'legacy');
        $rows = [$fixture['row'], $codec->generate(), $codec->generate()];
        foreach ($rows as $i => $cipherRow) {
            foreach ($rows as $j => $hashRow) {
                if ($i !== $j) {
                    $this->assertUnavailable(fn() => $codec->decrypt($cipherRow['sn_ciphertext'], 'legacy', $hashRow['sn_hash']));
                }
            }
        }
    }

    public function testCiphertextAcceptsOnly44Or60BytesAndStillChecksThePlaintextHash(): void
    {
        $fixture = $this->legacyFixture();
        $codec = new DeviceSnCredential(['legacy' => $fixture['key']], 'legacy');
        foreach (['', 'not-base64!', base64_encode(str_repeat('x', 44)), base64_encode(str_repeat('x', 60))] as $ciphertext) {
            $this->assertUnavailable(fn() => $codec->decrypt($ciphertext, 'legacy', $fixture['row']['sn_hash']));
        }
        // Correctly authenticated records with unsupported plaintext lengths must
        // be rejected too, rather than merely failing because of a damaged tag.
        foreach ([0, 1, 15, 17, 24, 31, 33, 48] as $length) {
            $sn = str_repeat('A', $length);
            $hash = hash('sha256', $sn);
            $ciphertext = $this->authenticatedRecord($sn, $fixture['key'], $hash);
            $this->assertUnavailable(fn() => $codec->decrypt($ciphertext, 'legacy', $hash));
        }
        foreach ([16, 32] as $length) {
            $hash = str_repeat('0', 64);
            $ciphertext = $this->authenticatedRecord(str_repeat('A', $length), $fixture['key'], $hash);
            $this->assertUnavailable(fn() => $codec->decrypt($ciphertext, 'legacy', $hash));
        }
    }

    public function testKeyRotationRetainsOld32And16CharacterCiphertext(): void
    {
        $fixture = $this->legacyFixture();
        $keys = ['legacy' => $fixture['key'], 'new' => base64_encode(random_bytes(32))];
        $oldRows = [$fixture['row'], (new DeviceSnCredential($keys, 'legacy'))->generate()];
        $rotated = new DeviceSnCredential($keys, 'new');
        $retired = new DeviceSnCredential(['new' => $keys['new']], 'new');
        foreach ($oldRows as $old) {
            self::assertSame($old['sn'], $rotated->decrypt($old['sn_ciphertext'], 'legacy', $old['sn_hash']));
            $this->assertUnavailable(fn() => $retired->decrypt($old['sn_ciphertext'], 'legacy', $old['sn_hash']));
        }
        $new = $rotated->generate();
        self::assertSame('new', $new['key_id']);
        self::assertSame(19, strlen($new['sn']));
        self::assertSame($new['sn'], $rotated->decrypt($new['sn_ciphertext'], 'new', $new['sn_hash']));
    }

    public function testBothSnLengthsAreRedactedWithoutHidingRecordIds(): void
    {
        foreach ([16, 32] as $length) {
            foreach ([str_repeat('A', $length), DeviceSnCredential::format(str_repeat('A', $length))] as $sn) {
                self::assertStringNotContainsString($sn, LogFilter::filter('{"sn":"' . $sn . '"}'));
                self::assertStringNotContainsString($sn, LogFilter::filter('sn=' . $sn));
                $safe = LogFilter::filterArray(['sn' => $sn, 'device_sn_id' => 3, 'sn_tail' => 'AAAA']);
                self::assertSame('[FILTERED]', $safe['sn']);
                self::assertSame(3, $safe['device_sn_id']);
                self::assertSame('AAAA', $safe['sn_tail']);
            }
        }
    }

    private function legacyFixture(): array
    {
        return require dirname(__DIR__, 2) . '/fixtures/device-sn-legacy.php';
    }

    private function authenticatedRecord(string $plaintext, string $key, string $hash): string
    {
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', base64_decode($key, true), OPENSSL_RAW_DATA,
            $nonce, $tag, 'device-sn:v1:legacy:' . $hash, 16);
        self::assertNotFalse($ciphertext);
        return base64_encode($nonce . $tag . $ciphertext);
    }

    private function assertUnavailable(callable $action): void
    {
        try {
            $action();
            self::fail('Invalid encrypted SN accepted.');
        } catch (HttpException $exception) {
            self::assertSame(503, $exception->statusCode);
        }
    }
}
