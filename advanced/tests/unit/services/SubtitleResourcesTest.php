<?php

namespace tests\unit\services;

use api\modules\v1\services\SubtitleResources;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\db\Connection;
use yii\web\UnprocessableEntityHttpException;

final class SubtitleResourcesTest extends TestCase
{
    public function testListReturnsNumericMetadataForBothStoredJsonShapes(): void
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $db->createCommand('CREATE TABLE resource (id INTEGER PRIMARY KEY, file_id INTEGER,
            name TEXT, created_at TEXT, type TEXT, author_id INTEGER, info TEXT)')->execute();
        $summary = ['cueCount' => 287, 'durationMs' => 596166];
        $object = json_encode($summary, JSON_THROW_ON_ERROR);
        foreach ([$object, json_encode($object, JSON_THROW_ON_ERROR)] as $info) {
            $db->createCommand()->insert('resource', [
                'file_id' => 3, 'name' => '字幕', 'type' => 'subtitle', 'author_id' => 7, 'info' => $info,
            ])->execute();
        }
        $list = (new SubtitleResources($db, 7))->listing(1);
        self::assertSame([$summary, $summary], array_column($list['items'], 'info'));
    }

    public function testSearchCountsAndPagesOnlyTheOwnersMatchingSubtitles(): void
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $db->createCommand('CREATE TABLE resource (id INTEGER PRIMARY KEY, file_id INTEGER,
            name TEXT, created_at TEXT, type TEXT, author_id INTEGER, info TEXT)')->execute();
        $rows = [];
        for ($i = 1; $i <= 21; $i++) {
            $rows[] = ["段落 {$i} 海洋", 'subtitle', 7, '{}'];
        }
        $rows[] = ['城市', 'subtitle', 7, '{}'];
        $rows[] = ['海洋音频', 'audio', 7, '{}'];
        $rows[] = ['他人的海洋', 'subtitle', 8, '{}'];
        $db->createCommand()->batchInsert('resource', ['name', 'type', 'author_id', 'info'], $rows)->execute();
        $service = new SubtitleResources($db, 7);
        $first = $service->listing(1, ' 海洋 ');
        self::assertSame(21, $first['total']);
        self::assertCount(20, $first['items']);
        self::assertSame(['段落 1 海洋'], array_column($service->listing(2, '海洋')['items'], 'name'));
        self::assertSame(['items' => [], 'total' => 0], $service->listing(1, '不存在'));
    }

    public function testNormalizesUtf8MultilineAndOverlappingCues(): void
    {
        $source = "\xEF\xBB\xBF2\r\n00:00:02.001 --> 00:00:03.002\r\nB\r\n\r\n"
            . "1\r\n00:00:01,000 --> 00:00:03,000\r\n你好🌍\r\nWorld";
        self::assertSame([
            ['startMs' => 1000, 'endMs' => 3000, 'text' => "你好🌍\nWorld"],
            ['startMs' => 2001, 'endMs' => 3002, 'text' => 'B'],
        ], SubtitleResources::parseSrt($source));
    }

    public static function invalidDocuments(): iterable
    {
        yield [''];
        yield ["1\n00:00:00,500 --> 00:00:00,250\nA"];
        yield ["1\n00:60:00,000 --> 01:01:00,000\nA"];
        yield ["1\n00:00:00,000 --> 00:00:01,000"];
        yield ["1\n00:00:00,000 --> 00:00:01,000\n\xff"];
        yield [str_repeat('a', 1048577)];
        yield [str_repeat("1\n00:00:00,000 --> 00:00:01,000\nA\n\n", 10001)];
    }

    #[DataProvider('invalidDocuments')]
    public function testRejectsInvalidOrOversizeDocument(string $source): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        SubtitleResources::parseSrt($source);
    }
}
