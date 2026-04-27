<?php

namespace tests\unit\migrations;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

final class ArSlamSpaceRbacMigrationTest extends TestCase
{
    private $originalDbComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();

        Yii::$app->db->createCommand()->createTable('{{%auth_item}}', [
            'name' => 'string not null primary key',
            'type' => 'integer not null',
            'description' => 'text',
            'rule_name' => 'string',
            'data' => 'blob',
            'created_at' => 'integer',
            'updated_at' => 'integer',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%auth_item_child}}', [
            'parent' => 'string not null',
            'child' => 'string not null',
            'PRIMARY KEY (parent, child)',
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testMigrationRegistersRoutePermissionsAndAttachesThemToExistingGroups(): void
    {
        $migrationPath = dirname(__DIR__, 3)
            . '/console/migrations/m260427_010000_register_ar_slam_space_rbac_permissions.php';
        $this->assertFileExists($migrationPath);
        require_once $migrationPath;

        $migration = new \m260427_010000_register_ar_slam_space_rbac_permissions();
        $migration->safeUp();

        $permissionNames = Yii::$app->db->createCommand(
            "SELECT name FROM auth_item WHERE type = 2 ORDER BY name"
        )->queryColumn();

        $this->assertContains('@restful/v1/space/create', $permissionNames);
        $this->assertContains('@restful/v1/space/index', $permissionNames);
        $this->assertContains('@restful/v1/space/view', $permissionNames);
        $this->assertContains('@restful/v1/space/delete', $permissionNames);
        $this->assertContains('@restful/v1/verse-space/index', $permissionNames);
        $this->assertContains('@restful/v1/verse-space/view', $permissionNames);
        $this->assertContains('@restful/v1/plugin-ar-slam-localization/bindings', $permissionNames);
        $this->assertContains('@restful/v1/plugin-ar-slam-localization/create-bindings', $permissionNames);

        $links = Yii::$app->db->createCommand(
            "SELECT parent || '=>' || child FROM auth_item_child ORDER BY parent, child"
        )->queryColumn();

        $this->assertContains('user=>基础操作', $links);
        $this->assertContains('基础操作=>绑定权限', $links);
        $this->assertContains('基础操作=>自有空间', $links);
        $this->assertContains('基础操作=>@restful/v1/space/create', $links);
        $this->assertContains('基础操作=>@restful/v1/space/index', $links);
        $this->assertContains('自有空间=>@restful/v1/space/view', $links);
        $this->assertContains('自有空间=>@restful/v1/space/delete', $links);
        $this->assertContains('绑定权限=>@restful/v1/verse-space/index', $links);
        $this->assertContains('绑定权限=>@restful/v1/verse-space/view', $links);
        $this->assertContains('绑定权限=>@restful/v1/plugin-ar-slam-localization/bindings', $links);
        $this->assertContains('绑定权限=>@restful/v1/plugin-ar-slam-localization/create-bindings', $links);
    }
}
