<?php

namespace tests\unit\services;

use api\modules\v1\services\SubtitleFiles;
use common\components\Secret;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\UnprocessableEntityHttpException;

final class SubtitleFilesTest extends TestCase
{
    private const HOST = 'fixture-1250000000.cos.ap-nanjing.myqcloud.com';
    private const HASH = '0123456789abcdef0123456789abcdef';
    private mixed $previousSecret;

    protected function setUp(): void
    {
        $this->previousSecret = Yii::$app->get('secret', false);
        Yii::$app->set('secret', new Secret([
            'id' => 'test-id', 'key' => 'test-signing-key',
            'cloud' => [
                'private' => ['bucket' => 'fixture-1250000000', 'region' => 'ap-nanjing'],
                'public' => ['bucket' => 'public-1250000000', 'region' => 'ap-beijing'],
            ],
        ]));
    }

    protected function tearDown(): void
    {
        Yii::$app->set('secret', $this->previousSecret);
    }

    public function testSignsTheCapturedObjectForFiveMinutes(): void
    {
        $key = self::HASH . '.json';
        $url = 'https://' . self::HOST . '/subtitles/7/' . $key;
        $start = time();
        $timezone = date_default_timezone_get();
        $signed = SubtitleFiles::signedDownloadUrl(7, ['key' => $key, 'md5' => self::HASH, 'url' => $url]);
        self::assertSame($timezone, date_default_timezone_get());
        self::assertNull(SubtitleFiles::publicUrl(7, ['key' => $key, 'md5' => self::HASH, 'url' => $url]));
        self::assertSame($url, explode('?', $signed, 2)[0]);
        parse_str(parse_url($signed, PHP_URL_QUERY), $query);
        parse_str($query['sign'], $authorization);
        $expires = (int) explode(';', $authorization['q-sign-time'])[1];
        self::assertGreaterThanOrEqual($start + 300, $expires);
        self::assertLessThanOrEqual(time() + 300, $expires);
        self::assertSame('private, no-store', $query['response-cache-control']);
    }

    public function testPublicCosUrlUsesConfiguredRegionAndHasNoSignature(): void
    {
        $key = self::HASH . '.json';
        $url = 'https://public-1250000000.cos.ap-beijing.myqcloud.com/subtitles/7/' . $key;
        $file = ['key' => $key, 'md5' => self::HASH, 'url' => $url . '?old-query'];
        self::assertSame($url, SubtitleFiles::publicUrl(7, $file));
        self::assertNull(SubtitleFiles::signedDownloadUrl(7, $file));
    }

    public function testNewUploadsRequirePublicStorage(): void
    {
        $key = self::HASH . '.json';
        $this->expectException(UnprocessableEntityHttpException::class);
        SubtitleFiles::read(7, ['key' => $key, 'md5' => self::HASH,
            'url' => 'https://' . self::HOST . '/subtitles/7/' . $key], requirePublic: true);
    }

    public function testLocalContentAndLegacyUuidFilesUseTheLocalResponse(): void
    {
        $key = self::HASH . '.json';
        self::assertNull(SubtitleFiles::signedDownloadUrl(7, ['key' => $key, 'md5' => self::HASH,
            'url' => 'http://localhost:8081/storage/raw/subtitles/7/' . $key]));
        self::assertNull(SubtitleFiles::signedDownloadUrl(7, ['key' => 'subtitles/7/12345678-1234-5678-1234-123456789abc.json',
            'md5' => self::HASH, 'url' => null]));
    }

    public function testRefusesToSignAFileOutsideTheOwnersDirectory(): void
    {
        $key = self::HASH . '.json';
        $this->expectException(UnprocessableEntityHttpException::class);
        SubtitleFiles::signedDownloadUrl(7, ['key' => $key, 'md5' => self::HASH,
            'url' => 'https://' . self::HOST . '/subtitles/8/' . $key]);
    }
}
