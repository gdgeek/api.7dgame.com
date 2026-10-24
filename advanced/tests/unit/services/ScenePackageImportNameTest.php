<?php

namespace tests\unit\services;

use api\modules\v1\services\ScenePackageService;
use PHPUnit\Framework\TestCase;
use yii\validators\StringValidator;

class ScenePackageImportNameTest extends TestCase
{
    private function importName(string $name, ?\DateTimeInterface $importedAt = null): string
    {
        $method = new \ReflectionMethod(ScenePackageService::class, 'buildImportName');
        return $method->invoke(new ScenePackageService(), $name, $importedAt);
    }

    public function testResourcesStillLoseLegacyCopySuffixesWithoutAddingTimestamps(): void
    {
        $name = '素材.glb' . str_repeat('（副本 2026-09-11 15:15:26）', 10);
        $result = $this->importName($name);
        $this->assertSame('素材.glb', $result);
        $this->assertSame($result, $this->importName($result));
    }

    public function testLongUnicodeNamesFitTheModelLimitWithoutCorruption(): void
    {
        foreach (['a', '中', '😀'] as $character) {
            $result = $this->importName(str_repeat($character, 280));
            $this->assertSame(255, mb_strlen($result, 'UTF-8'));
            $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
            $validator = new StringValidator(['max' => 255, 'encoding' => 'UTF-8']);
            $this->assertTrue($validator->validate($result));
        }
    }

    public function testOrdinaryNameAndNonGeneratedSuffixArePreserved(): void
    {
        $name = '展品（副本说明）';
        $this->assertSame($name, $this->importName($name));
    }

    public function testRepeatedSceneCopiesReplaceTheTimestamp(): void
    {
        $name = '我的_场景_2026-06-30_11-00-05';
        $startedAt = new \DateTimeImmutable('2026-10-24 13:14:15', new \DateTimeZone('Asia/Shanghai'));
        for ($copy = 0; $copy < 10; $copy++) {
            $importedAt = $startedAt->modify("+{$copy} seconds");
            $name = $this->importName($name, $importedAt);
            $this->assertSame('我的_场景' . $importedAt->format('_Y-m-d_H-i-s'), $name);
        }
    }

    public function testRepeatedAndMixedHistoricalSuffixesAreRemovedBeforeRefreshing(): void
    {
        $importedAt = new \DateTimeImmutable('2026-10-24 13:14:15', new \DateTimeZone('Asia/Shanghai'));
        foreach ([
            str_repeat('（副本 2026-09-11 15:15:26）', 3),
            str_repeat('_2024-02-29_23-59-58', 3),
            '_2024-02-29_23-59-58（副本 2026-09-11 15:15:26）_2026-09-12_01-02-03',
        ] as $suffixes) {
            $this->assertSame('展品_2026-10-24_13-14-15', $this->importName('展品' . $suffixes, $importedAt));
        }
    }

    public function testOneBatchTimeIsSharedBySceneAndEntityNamesInItsTimezone(): void
    {
        $importedAt = (new \DateTimeImmutable('2026-10-23T17:05:06Z'))
            ->setTimezone(new \DateTimeZone('Asia/Shanghai'));
        foreach (['场景_2026-06-30_11-00-05', '组件一', '组件二（副本 2026-09-11 15:15:26）'] as $name) {
            $this->assertStringEndsWith('_2026-10-24_01-05-06', $this->importName($name, $importedAt));
        }
    }

    public function testBusinessDatesAndNonGeneratedCopyDescriptionsRemainInTheTitle(): void
    {
        $name = '2026-09-21展览（副本说明）';
        $importedAt = new \DateTimeImmutable('2026-10-24T01:02:03Z');
        $this->assertSame($name . '_2026-10-24_01-02-03', $this->importName($name, $importedAt));
    }

    public function testInvalidDateOrTimeSuffixesAreNotRemoved(): void
    {
        $importedAt = new \DateTimeImmutable('2026-10-24T01:02:03Z');
        foreach ([
            '展品_2026-02-29_12-00-00',
            '展品_2024-02-30_12-00-00',
            '展品_2026-01-01_24-00-00',
            '展品_2026-01-01_12-60-00',
            '展品_2026-01-01_12-00-60',
            '展品（副本 2026-02-29 12:00:00）',
            '展品（副本 2026-01-01 24:00:00）',
        ] as $name) {
            $this->assertSame($name . '_2026-10-24_01-02-03', $this->importName($name, $importedAt));
            $this->assertSame($name, $this->importName($name));
        }
    }

    public function testResourceCanonicalSuffixesRemainUnchanged(): void
    {
        $name = '素材_2024-02-29_23-59-58';
        $this->assertSame($name, $this->importName($name));
        $this->assertSame($name, $this->importName($name . '（副本 2026-09-11 15:15:26）'));
    }

    public function testLongUnicodeSceneNamesReserveSpaceForTheFreshSuffix(): void
    {
        $importedAt = new \DateTimeImmutable('2026-10-24T01:02:03Z');
        foreach (['a', '中', '😀'] as $character) {
            $result = $this->importName(str_repeat($character, 280) . '_2024-02-29_23-59-58', $importedAt);
            $this->assertSame(str_repeat($character, 235) . '_2026-10-24_01-02-03', $result);
            $this->assertSame(255, mb_strlen($result, 'UTF-8'));
            $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
            $validator = new StringValidator(['max' => 255, 'encoding' => 'UTF-8']);
            $this->assertTrue($validator->validate($result));
        }
    }

    public function testHistoricalAppCopySuffixesAndTimestampsAreRecursivelyReplaced(): void
    {
        $importedAt = new \DateTimeImmutable('2026-10-24T01:02:03Z');
        foreach ([' - 副本', ' - Copy', ' - コピー', ' - สำเนา'] as $copySuffix) {
            foreach ([
                '组件_2026-06-30_11-00-05' . $copySuffix,
                '组件' . $copySuffix . '_2026-06-30_11-00-05' . $copySuffix,
                '组件' . $copySuffix . $copySuffix . '_2026-06-30_11-00-05',
            ] as $name) {
                $this->assertSame('组件_2026-10-24_01-02-03', $this->importName($name, $importedAt));
            }
        }
        $mixedName = '  组件 - Copy_2026-06-30_11-00-05 - 副本'
            . '（副本 2026-09-11 15:15:26） - コピー_2026-09-12_01-02-03 - สำเนา  ';
        $this->assertSame('组件_2026-10-24_01-02-03', $this->importName($mixedName, $importedAt));
    }

    public function testSceneAndEntityNamesCannotBecomeOnlyATimestamp(): void
    {
        $importedAt = new \DateTimeImmutable('2026-10-24T01:02:03Z');
        foreach ([
            '',
            " \t\n ",
            '_2026-06-30_11-00-05',
            '（副本 2026-09-11 15:15:26）',
            ' _2026-06-30_11-00-05（副本 2026-09-11 15:15:26） - Copy ',
        ] as $name) {
            try {
                $this->importName($name, $importedAt);
                $this->fail('An empty scene or entity base name must be rejected.');
            } catch (\InvalidArgumentException $error) {
                $this->assertStringContainsString('non-empty base name', $error->getMessage());
            }
        }
    }

    public function testResourceAppCopySuffixesAndWhitespaceRemainUnchanged(): void
    {
        $name = '  素材_2026-06-30_11-00-05 - 副本 - Copy - コピー - สำเนา  ';
        $this->assertSame($name, $this->importName($name));
        $this->assertSame(' ', $this->importName(' '));
    }
}
