<?php

namespace api\modules\v1\services;

use common\components\Storage;
use Qcloud\Cos\Client;
use Yii;
use yii\web\UnprocessableEntityHttpException;

final class SubtitleFiles
{
    private const DOWNLOAD_EXPIRY = '+5 minutes';

    /** @return array{string, string} Storage bucket alias and validated object key. */
    private static function location(int $owner, array $file): array
    {
        $hash = $file['md5'];
        $key = $file['key'];
        if (!is_string($hash) || !preg_match('/^[a-f0-9]{32}$/D', $hash)) {
            throw new \UnexpectedValueException('Invalid subtitle digest');
        }
        $directory = SubtitleResources::FILE_PREFIX . $owner . '/';
        // Historical versions used a UUID in the private local bucket.
        if (empty($file['url']) && preg_match('~^' . $directory . '[a-f0-9-]{36}\.json$~D', $key)) {
            return ['raw', $key];
        }
        if ($key !== $hash . '.json') {
            throw new UnprocessableEntityHttpException('字幕文件记录与 JSON 内容不匹配。');
        }
        $object = $directory . $key;
        $url = (string) $file['url'];
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === '/storage/raw/' . $object || $path === '/storage/store/' . $object) {
            return [str_starts_with($path, '/storage/store/') ? 'store' : 'raw', $object];
        }
        foreach (['public', 'private'] as $alias) {
            $bucket = Yii::$app->secret->cloud[$alias] ?? [];
            if (!empty($bucket['bucket']) && !empty($bucket['region']) && $path === '/' . $object
                && parse_url($url, PHP_URL_HOST) === self::cosHost($bucket)) {
                return [$alias, $object];
            }
        }
        throw new UnprocessableEntityHttpException('字幕文件必须来自素材库的字幕上传目录。');
    }

    private static function cosHost(array $bucket): string
    {
        return $bucket['bucket'] . '.cos.' . $bucket['region'] . '.myqcloud.com';
    }

    public static function publicUrl(int $owner, array $file): ?string
    {
        [$bucket, $object] = self::location($owner, $file);
        if ($bucket === 'public') {
            return 'https://' . self::cosHost(Yii::$app->secret->cloud['public']) . '/' . $object;
        }
        if ($bucket === 'store') {
            $url = (new Storage())->publicUrl('store', $object);
            return str_starts_with($url, '/') ? Yii::$app->urlManager->hostInfo . $url : $url;
        }
        return null;
    }

    public static function signedDownloadUrl(int $owner, array $file): ?string
    {
        [$bucket, $object] = self::location($owner, $file);
        if ($bucket !== 'private') return null;
        $private = Yii::$app->secret->cloud['private'];
        return self::cos($private)->getObjectUrl($private['bucket'], $object, self::DOWNLOAD_EXPIRY, [
            'ResponseCacheControl' => 'private, no-store',
        ]);
    }

    public static function read(int $owner, array $file, bool $requirePublic = false): string
    {
        [$bucket, $object] = self::location($owner, $file);
        if ($requirePublic && !in_array($bucket, ['store', 'public'], true)) {
            throw new UnprocessableEntityHttpException('请将字幕文件上传至公共素材桶。');
        }
        if ($bucket === 'store' || $bucket === 'raw') {
            $path = (new Storage())->path($bucket, $object);
            $json = is_file($path) ? file_get_contents($path) : false;
        } else {
            $config = Yii::$app->secret->cloud[$bucket];
            $json = (string) self::cos($config)->getObject(['Bucket' => $config['bucket'], 'Key' => $object, 'Range' => 'bytes=0-4194304'])['Body'];
        }
        if ($json === false || strlen($json) > 4194304 || md5($json) !== $file['md5']) {
            throw new UnprocessableEntityHttpException('字幕文件不存在或内容校验失败。');
        }
        return $json;
    }

    private static function cos(array $bucket): Client
    {
        $secret = Yii::$app->secret;
        if (!$secret->id || !$secret->key) {
            throw new \RuntimeException('COS storage is not configured');
        }
        return new Client(['region' => $bucket['region'], 'scheme' => 'https', 'timezone' => date_default_timezone_get(),
            'credentials' => ['secretId' => $secret->id, 'secretKey' => $secret->key]]);
    }
}
