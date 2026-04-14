<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\OrganizationController;
use api\modules\v1\models\Organization;
use api\modules\v1\models\UserOrganization;
use PHPUnit\Framework\TestCase;
use Yii;

final class OrganizationControllerTest extends TestCase
{
    public function testOrganizationArtifactsExposeExpectedContracts(): void
    {
        $this->assertSame('{{%organization}}', Organization::tableName());
        $this->assertSame('{{%user_organization}}', UserOrganization::tableName());

        $controller = new OrganizationController('organization', Yii::$app->getModule('v1'));
        $method = new \ReflectionMethod($controller, 'verbs');
        $method->setAccessible(true);
        $verbs = $method->invoke($controller);

        $this->assertSame(['GET'], $verbs['list']);
        $this->assertSame(['POST'], $verbs['create']);
        $this->assertSame(['POST'], $verbs['update']);
        $this->assertSame(['POST'], $verbs['bind-user']);
        $this->assertSame(['POST'], $verbs['unbind-user']);
    }
}
