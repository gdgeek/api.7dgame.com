<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;

final class DebugRouteExposureTest extends TestCase
{
    public function testPublishedApiConfigDoesNotExposeDatabaseDebugRoute(): void
    {
        $config = file_get_contents(dirname(__DIR__, 4) . '/files/api/config/main.php');

        $this->assertIsString($config);
        $this->assertStringNotContainsString('db-debug', $config);
        $this->assertStringNotContainsString("'controller' => 'v1/test'", $config);
    }
}
