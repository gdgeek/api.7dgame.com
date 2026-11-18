<?php

namespace tests\unit\services;

use api\modules\v1\services\SubtitleResources;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\db\Connection;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

final class ResourceSubtitleBindingTest extends TestCase
{
    private Connection $db;
    private SubtitleResources $service;

    protected function setUp(): void
    {
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        $this->db->createCommand('PRAGMA foreign_keys = ON')->execute();
        $this->db->createCommand('CREATE TABLE resource (id INTEGER PRIMARY KEY, type TEXT, name TEXT, author_id INTEGER)')->execute();
        require_once dirname(__DIR__, 3) . '/console/migrations/m261118_000002_create_resource_subtitle.php';
        $migration = new \m261118_000002_create_resource_subtitle(['db' => $this->db, 'compact' => true]);
        $migration->safeUp();
        $migration->safeUp();
        $this->db->createCommand()->batchInsert('resource', ['id', 'type', 'name', 'author_id'], [
            [1, 'audio', '音频', 7], [2, 'video', '视频', 7], [3, 'subtitle', '字幕甲', 7],
            [4, 'subtitle', '字幕乙', 7], [5, 'picture', '图片', 7],
            [6, 'subtitle', '他人字幕', 8], [7, 'video', '他人视频', 8],
        ])->execute();
        $this->service = new SubtitleResources($this->db, 7);
    }

    private function save(int $media, ?int $target, ?int $previous): array
    {
        return $this->db->transaction(fn () => $this->service->setBinding($media, [
            'subtitleResourceId' => $target, 'previousSubtitleResourceId' => $previous,
        ]));
    }

    public function testReuseReplaceAndUnbind(): void
    {
        $first = ['subtitle' => ['id' => 3, 'name' => '字幕甲']];
        $empty = ['subtitle' => null];
        self::assertSame($empty, $this->service->binding(1));
        self::assertSame($first, $this->save(1, 3, null));
        self::assertSame($first, $this->save(1, 3, null)); // Retry after a lost response.
        self::assertSame($first, $this->save(2, 3, null));
        $this->save(1, 4, 3);
        self::assertSame(['subtitle' => ['id' => 4, 'name' => '字幕乙']], $this->service->binding(1));
        self::assertSame($empty, $this->save(1, null, 4));
        self::assertSame($empty, $this->service->binding(1));
        self::assertSame($first, $this->service->binding(2));
        self::assertSame(7, (int)$this->db->createCommand('SELECT COUNT(*) FROM resource')->queryScalar());
    }

    public function testDeletingEitherEndpointCleansOnlyItsBindings(): void
    {
        $this->save(1, 3, null);
        $this->save(2, 3, null);
        $this->db->createCommand()->delete('resource', ['id' => 1])->execute();
        self::assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM resource_subtitle')->queryScalar());
        $this->db->createCommand()->delete('resource', ['id' => 3])->execute();
        self::assertSame(['subtitle' => null], $this->service->binding(2));
        self::assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM resource_subtitle')->queryScalar());
    }

    public function testStaleReplacementCannotRemoveNewBinding(): void
    {
        $this->save(1, 3, null);
        $this->save(1, 4, 3);
        $this->expectException(ConflictHttpException::class);
        $this->save(1, null, 3);
    }

    public static function invalidEndpoints(): iterable
    {
        yield [5, 3];
        yield [7, 3];
        yield [1, 2];
        yield [1, 6];
    }

    #[DataProvider('invalidEndpoints')]
    public function testBothEndpointsMustHaveCorrectTypeAndOwner(int $media, int $subtitle): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->save($media, $subtitle, null);
    }

    public function testMissingTargetCannotClearAnExistingBinding(): void
    {
        $this->save(1, 3, null);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->service->setBinding(1, ['previousSubtitleResourceId' => 3]);
    }

    public static function rejectedUploads(): iterable
    {
        yield 'stale window' => [1, null, ConflictHttpException::class];
        yield 'invalid SRT' => [1, 3, UnprocessableEntityHttpException::class];
        yield 'non-media' => [5, null, NotFoundHttpException::class];
        yield 'different owner' => [7, null, NotFoundHttpException::class];
    }

    #[DataProvider('rejectedUploads')]
    public function testRejectedUploadPreservesTheBindingAndCreatesNoFile(int $media, ?int $previous, string $exception): void
    {
        $this->save(1, 3, null);
        $path = null;
        $this->expectException($exception);
        try {
            $this->service->uploadBound($media, [
                'name' => '字幕', 'srt' => 'invalid', 'previousSubtitleResourceId' => $previous,
            ], $path);
        } finally {
            self::assertNull($path);
            self::assertSame(3, $this->service->binding(1)['subtitle']['id']);
            self::assertSame(7, (int)$this->db->createCommand('SELECT COUNT(*) FROM resource')->queryScalar());
        }
    }
}
