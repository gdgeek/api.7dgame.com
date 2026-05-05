<?php

namespace tests\unit\models;

use api\modules\v1\models\Verse;
use PHPUnit\Framework\TestCase;

final class VerseTest extends TestCase
{
    public function testExtraFieldsExposeBoundSpace(): void
    {
        $extraFields = (new Verse())->extraFields();

        $this->assertContains('space', $extraFields);
    }
}
