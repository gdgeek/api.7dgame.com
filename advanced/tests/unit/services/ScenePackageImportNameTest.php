<?php

namespace tests\unit\services;

use api\modules\v1\services\ScenePackageService;
use PHPUnit\Framework\TestCase;
use yii\validators\StringValidator;

class ScenePackageImportNameTest extends TestCase
{
    private function importName(string $name): string
    {
        $method = new \ReflectionMethod(ScenePackageService::class, 'buildImportName');
        return $method->invoke(new ScenePackageService(), $name);
    }

    public function testRepeatedCopiesDoNotAccumulateTimestamps(): void
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
}
