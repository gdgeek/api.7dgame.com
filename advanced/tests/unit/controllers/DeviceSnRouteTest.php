<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;
use yii\web\Request;
use yii\web\UrlManager;

final class DeviceSnRouteTest extends TestCase
{
    public function testRealPublishedRoutesDispatchToExpectedActions(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $manager = new UrlManager($config['components']['urlManager']);
        foreach ([
            ['GET', 'v1/plugin-sn', 'v1/plugin-sn/index'],
            ['GET', 'v1/plugin-sn/accounts', 'v1/plugin-sn/accounts'],
            ['POST', 'v1/plugin-sn/generate', 'v1/plugin-sn/generate'],
            ['POST', 'v1/plugin-sn/export', 'v1/plugin-sn/export'],
            ['GET', 'v1/plugin-sn/5', 'v1/plugin-sn/view'],
            ['PATCH', 'v1/plugin-sn/5', 'v1/plugin-sn/update'],
            ['POST', 'v1/plugin-sn/5/reveal', 'v1/plugin-sn/reveal'],
            ['POST', 'v1/auth/sn-activate', 'v1/auth/sn-activate'],
            ['POST', 'v1/auth/sn-login', 'v1/auth/sn-login'],
            ['OPTIONS', 'v1/plugin-sn/5/reveal', 'v1/plugin-sn/options'],
        ] as [$method, $path, $expected]) {
            $request = new DeviceSnRouteRequest($method, $path);
            self::assertSame($expected, $manager->parseRequest($request)[0] ?? null, $method . ' ' . $path);
        }
    }
}

final class DeviceSnRouteRequest extends Request
{
    private string $testMethod;
    public function __construct(string $method, string $path)
    {
        $this->testMethod = $method;
        parent::__construct(['hostInfo' => 'http://localhost', 'scriptUrl' => '', 'pathInfo' => $path]);
    }
    public function getMethod() { return $this->testMethod; }
}
