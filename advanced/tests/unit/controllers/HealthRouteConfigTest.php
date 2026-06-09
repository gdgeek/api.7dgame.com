<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;

final class HealthRouteConfigTest extends TestCase
{
    public function testPublishedApiConfigRegistersGetAndHeadHealthRoutes(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];

        $this->assertContains('GET health', array_keys($rules));
        $this->assertContains('HEAD health', array_keys($rules));
        $this->assertSame('health/index', $rules['GET health'] ?? null);
        $this->assertSame('health/index', $rules['HEAD health'] ?? null);
    }
}
