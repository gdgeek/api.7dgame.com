<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;
use Yii;

final class PluginArSlamLocalizationRouteConfigTest extends TestCase
{
    public function testPublishedApiConfigRegistersArSlamLocalizationBindingRoutes(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];
        $routeRule = null;

        foreach ($rules as $rule) {
            if (is_array($rule) && ($rule['controller'] ?? null) === 'v1/plugin-ar-slam-localization') {
                $routeRule = $rule;
                break;
            }
        }

        $this->assertNotNull(
            $routeRule,
            'Published API config must expose the AR SLAM localization binding routes.'
        );
        $this->assertSame(['bindings', 'create-bindings', 'delete-binding'], $routeRule['only'] ?? null);
        $this->assertSame(
            [
                'GET bindings' => 'bindings',
                'POST bindings' => 'create-bindings',
                'DELETE bindings/<verseId:\d+>' => 'delete-binding',
            ],
            $routeRule['extraPatterns'] ?? null
        );
    }

    public function testArSlamLocalizationControllerPublishesExpectedVerbs(): void
    {
        $controller = new \api\modules\v1\controllers\PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );
        $verbs = (new \ReflectionMethod($controller, 'verbs'))->invoke($controller);

        $this->assertSame(['GET'], $verbs['bindings'] ?? null);
        $this->assertSame(['POST'], $verbs['create-bindings'] ?? null);
        $this->assertSame(['DELETE'], $verbs['delete-binding'] ?? null);
    }

    public function testPublishedApiConfigRegistersSpaceRestResources(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];
        $controllers = [];

        foreach ($rules as $rule) {
            if (!is_array($rule) || !isset($rule['controller'])) {
                continue;
            }
            foreach ((array) $rule['controller'] as $controller) {
                $controllers[] = $controller;
            }
        }

        $this->assertContains('v1/space', $controllers);
        $this->assertContains('v1/verse-space', $controllers);
    }
}
