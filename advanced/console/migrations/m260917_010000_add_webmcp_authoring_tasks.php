<?php

use yii\db\Migration;
/** Additive task storage; rollback code without deleting recovery evidence. */
class m260917_010000_add_webmcp_authoring_tasks extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%webmcp_authoring_task}}', true) === null) {
            $this->createTable('{{%webmcp_authoring_task}}', [
                'task_id' => $this->string(36)->notNull(),
                'actor_id' => $this->integer()->notNull(),
                'name' => $this->string(120)->notNull(),
                'plan' => $this->db->driverName === 'mysql' ? 'MEDIUMTEXT NOT NULL' : $this->text()->notNull(),
                'progress' => $this->db->driverName === 'mysql' ? 'MEDIUMTEXT NOT NULL' : $this->text()->notNull(),
                'revision' => $this->integer()->notNull(),
                'lease_id' => $this->string(36),
                'lease_until' => $this->integer()->notNull()->defaultValue(0),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'PRIMARY KEY (task_id)',
            ], $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null);
        }
        $columns = [
            'task_id',
            'actor_id',
            'name',
            'plan',
            'progress',
            'revision',
            'lease_id',
            'lease_until',
            'created_at',
            'updated_at',
        ];
        if (array_diff($columns, $this->db->getTableSchema('{{%webmcp_authoring_task}}', true)->columnNames)) {
            throw new RuntimeException('Incomplete authoring task schema');
        }
        $indexes = $this->db->schema->getTableIndexes('{{%webmcp_authoring_task}}', true);
        $found = false;
        foreach ($indexes as $index) {
            if ($index->name === 'idx_webmcp_task_actor_updated') {
                if ($index->columnNames !== ['actor_id', 'updated_at']) {
                    throw new RuntimeException('Unexpected task index');
                }
                $found = true;
            }
        }
        if (!$found) {
            $this->createIndex('idx_webmcp_task_actor_updated', '{{%webmcp_authoring_task}}', ['actor_id', 'updated_at']);
        }
        $this->assertSchema();
        if ($this->db->getTableSchema('{{%auth_item}}', true) !== null) {
            foreach (['index', 'view', 'create', 'claim', 'checkpoint'] as $action) {
                $route = "@restful/v1/authoring-task/{$action}";
                $this->upsert('{{%auth_item}}', [
                    'name' => $route,
                    'type' => 2,
                    'description' => 'Access own WebMCP task evidence',
                    'created_at' => time(),
                    'updated_at' => time(),
                ], false);
                foreach (['meta', 'verse'] as $controller) {
                    foreach (['create', 'update'] as $parentAction) {
                        $parent = "@restful/v1/{$controller}/{$parentAction}";
                        if ((new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $parent])->exists($this->db)) {
                            $this->upsert('{{%auth_item_child}}', ['parent' => $parent, 'child' => $route], false);
                        }
                    }
                }
            }
            foreach (['meta', 'verse'] as $controller) {
                $route = "@restful/v1/{$controller}/create-operation";
                $parent = "@restful/v1/{$controller}/create";
                $this->upsert('{{%auth_item}}', [
                    'name' => $route,
                    'type' => 2,
                    'description' => 'Read own object creation receipt',
                    'created_at' => time(),
                    'updated_at' => time(),
                ], false);
                if ((new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $parent])->exists($this->db)) {
                    $this->upsert('{{%auth_item_child}}', ['parent' => $parent, 'child' => $route], false);
                }
            }
            Yii::$app->authManager?->invalidateCache();
        }
    }
    public function assertSchema(): void
    {
        $schema = $this->db->getTableSchema('{{%webmcp_authoring_task}}', true);
        $required = [
            'task_id',
            'actor_id',
            'name',
            'plan',
            'progress',
            'revision',
            'lease_id',
            'lease_until',
            'created_at',
            'updated_at',
        ];
        if (!$schema || array_diff($required, $schema->columnNames) || $schema->primaryKey !== ['task_id']) {
            throw new RuntimeException('Incomplete authoring task schema or primary key');
        }
        if ($this->db->driverName === 'mysql') {
            $table = $this->db->schema->getRawTableName('{{%webmcp_authoring_task}}');
            $engine = $this->db->createCommand('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:name', [':name' => $table])->queryScalar();
            if (strtolower((string) $engine) !== 'innodb') {
                throw new RuntimeException('Authoring tasks require InnoDB transactions');
            }
        }
    }
    public function safeDown()
    {
        echo "Task recovery evidence is retained; roll back application code instead.\n";
        return false;
    }
}
