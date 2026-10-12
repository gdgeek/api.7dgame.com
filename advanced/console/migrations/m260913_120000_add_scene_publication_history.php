<?php

use yii\db\Migration;

/** Additive rollout: deploy this schema and RBAC before enabling the new web client. */
class m260913_120000_add_scene_publication_history extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%scene_publication_revision}}', true) === null) {
            $this->createTable('{{%scene_publication_revision}}', [
                'id' => $this->bigPrimaryKey(), 'publication_version_id' => $this->string(36)->notNull(),
                'scene_id' => $this->integer()->notNull(), 'snapshot_id' => $this->integer()->notNull(),
                'actor_id' => $this->integer()->notNull(), 'operation_id' => $this->string(36),
                'source_server_revision' => $this->string(71)->notNull(), 'schema_version' => $this->integer()->notNull(),
                'language' => $this->string(8)->notNull(),
                'canonical_body' => $this->db->driverName === 'mysql' ? 'MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL' : $this->text()->notNull(),
                'content_hash' => $this->string(71)->notNull(), 'byte_length' => $this->integer()->notNull(),
                'created_at' => $this->integer()->notNull(),
            ], $this->db->driverName === 'mysql' ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin' : null);
        }
        $this->ensureIndex('uq_publication_version', '{{%scene_publication_revision}}', ['publication_version_id'], true);
        $this->ensureIndex('uq_publication_actor_operation', '{{%scene_publication_revision}}', ['actor_id', 'operation_id'], true);
        $this->ensureIndex('ix_publication_scene_id', '{{%scene_publication_revision}}', ['scene_id', 'id'], false);
        // Attach history routes beneath existing view/update grants. Current scene
        // editable checks still apply to every history request.
        if ($this->db->getTableSchema('{{%auth_item}}', true) !== null) {
            foreach (['verse' => ['publications', 'publication-version']] as $controller => $actions) {
                foreach ($actions as $action) {
                    $route = "@restful/v1/$controller/$action";
                    $this->upsert('{{%auth_item}}', ['name' => $route, 'type' => 2, 'description' => 'Read WebMCP server evidence', 'created_at' => time(), 'updated_at' => time()], false);
                    foreach (['view', 'update'] as $parentAction) {
                        $parent = "@restful/v1/$controller/$parentAction";
                        if ((new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $parent])->exists($this->db)) {
                            $this->upsert('{{%auth_item_child}}', ['parent' => $parent, 'child' => $route], false);
                        }
                    }
                }
            }
            Yii::$app->authManager?->invalidateCache();
        }
        $this->assertSchema();
    }

    public function assertSchema(): void
    {
        $table = '{{%scene_publication_revision}}';
        $schema = $this->db->getTableSchema($table, true);
        $columns = ['id', 'publication_version_id', 'scene_id', 'snapshot_id', 'actor_id', 'operation_id',
            'source_server_revision', 'schema_version', 'language', 'canonical_body', 'content_hash', 'byte_length', 'created_at'];
        if ($schema === null || array_diff($columns, $schema->columnNames)) {
            throw new \RuntimeException('Incomplete publication history schema');
        }
        foreach ($columns as $column) {
            if ($column !== 'operation_id' && $schema->columns[$column]->allowNull) {
                throw new \RuntimeException('Nullable publication evidence field: ' . $column);
            }
        }
        if (!$schema->columns['operation_id']->allowNull || $schema->primaryKey !== ['id']) {
            throw new \RuntimeException('Publication history requires an id primary key and nullable legacy operation id');
        }
        if ($this->db->driverName === 'mysql') {
            $body = $this->db->createCommand('SELECT DATA_TYPE, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column', [
                ':table' => $this->db->tablePrefix . 'scene_publication_revision', ':column' => 'canonical_body',
            ])->queryOne();
            if (!$body || !in_array(strtolower($body['DATA_TYPE']), ['mediumtext', 'longtext'], true) || $body['COLLATION_NAME'] !== 'utf8mb4_bin') {
                throw new \RuntimeException('Publication body requires binary-collated UTF-8 storage of at least 8 MiB');
            }
        }
        foreach ([
            ['uq_publication_version', ['publication_version_id'], true],
            ['uq_publication_actor_operation', ['actor_id', 'operation_id'], true],
            ['ix_publication_scene_id', ['scene_id', 'id'], false],
        ] as [$name, $fields, $unique]) {
            $found = array_filter($this->db->schema->getTableIndexes($table, true), static fn ($index) =>
                $index->name === $name && $index->columnNames === $fields && (bool) $index->isUnique === $unique);
            if (!$found) throw new \RuntimeException('Missing publication constraint: ' . $name);
        }
    }

    private function ensureIndex(string $name, string $table, array $columns, bool $unique): void
    {
        // MySQL DDL can commit before the migration history row. Recover a missing
        // index on rerun, but never accept a same-name index with weaker guarantees.
        foreach ($this->db->schema->getTableIndexes($table, true) as $index) {
            if ($index->name === $name) {
                if ($index->columnNames !== $columns || (bool) $index->isUnique !== $unique) {
                    throw new \RuntimeException("Unexpected index definition: $name");
                }
                return;
            }
        }
        $this->createIndex($name, $table, $columns, $unique);
    }

    public function safeDown()
    {
        echo "Publication archives and permissions are intentionally retained (no cascade on scene deletion). Roll back application code, not evidence.\n";
        return false;
    }
}
