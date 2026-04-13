<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;
use Yii;

final class OrganizationRouteConfigTest extends TestCase
{
    public function testPublishedApiConfigRegistersOrganizationRoutes(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];
        $organizationRule = null;

        foreach ($rules as $rule) {
            if (is_array($rule) && ($rule['controller'] ?? null) === 'v1/organization') {
                $organizationRule = $rule;
                break;
            }
        }

        $this->assertNotNull(
            $organizationRule,
            'Published API config must expose the v1/organization routes used by user-management.'
        );
        $this->assertSame(
            ['list', 'create', 'update', 'bind-user', 'unbind-user'],
            $organizationRule['only'] ?? null
        );
        $this->assertSame(
            [
                'GET list' => 'list',
                'POST create' => 'create',
                'POST update' => 'update',
                'POST bind-user' => 'bind-user',
                'POST unbind-user' => 'unbind-user',
            ],
            $organizationRule['extraPatterns'] ?? null
        );
    }

    public function testOrganizationControllerExistsAndPublishesExpectedVerbs(): void
    {
        $this->assertTrue(
            class_exists(\api\modules\v1\controllers\OrganizationController::class),
            'OrganizationController must exist for the published organization routes to resolve.'
        );

        $controller = new \api\modules\v1\controllers\OrganizationController('organization', Yii::$app);
        $verbs = (new \ReflectionMethod($controller, 'verbs'))->invoke($controller);

        $this->assertSame(['GET'], $verbs['list'] ?? null);
        $this->assertSame(['POST'], $verbs['create'] ?? null);
        $this->assertSame(['POST'], $verbs['update'] ?? null);
        $this->assertSame(['POST'], $verbs['bind-user'] ?? null);
        $this->assertSame(['POST'], $verbs['unbind-user'] ?? null);
    }
}
