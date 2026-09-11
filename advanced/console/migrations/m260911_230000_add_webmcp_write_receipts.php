<?php

use yii\db\Migration;

/** Additive rollout: deploy this schema and RBAC before enabling the new web client. */
class m260911_230000_add_webmcp_write_receipts extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%webmcp_operation}}', true) === null) {
            $this->createTable('{{%webmcp_operation}}', [
                'id' => $this->bigPrimaryKey(), 'actor_id' => $this->integer()->notNull(),
                'operation_id' => $this->string(36)->notNull(), 'target_type' => $this->string(16)->notNull(),
                'target_id' => $this->integer()->notNull(), 'action' => $this->string(20)->notNull(),
                'request_hash' => $this->string(71)->notNull(), 'receipt' => $this->text()->notNull(),
                'created_at' => $this->integer()->notNull(),
            ]);
        }
        $this->ensureIndex('uq_webmcp_actor_operation', '{{%webmcp_operation}}', ['actor_id', 'operation_id'], true);
        if ($this->db->getTableSchema('{{%scene_publication_revision}}', true) === null) {
            $this->createTable('{{%scene_publication_revision}}', [
                'id' => $this->bigPrimaryKey(), 'revision' => $this->string(36)->notNull(),
                'verse_id' => $this->integer()->notNull(), 'snapshot_id' => $this->integer()->notNull(),
                'snapshot_uuid' => $this->string(255), 'source_revision' => $this->string(71)->notNull(),
                'content_hash' => $this->string(71)->notNull(), 'snapshot_hash' => $this->string(71)->notNull(),
                'snapshot_json' => $this->db->driverName === 'mysql' ? 'LONGTEXT NOT NULL' : $this->text()->notNull(),
                'created_by' => $this->integer()->notNull(), 'created_at' => $this->integer()->notNull(),
            ]);
        }
        $this->ensureIndex('uq_scene_publication_revision', '{{%scene_publication_revision}}', ['revision'], true);
        $this->ensureIndex('idx_scene_publication_latest', '{{%scene_publication_revision}}', ['verse_id', 'id'], false);
        // Attach read-only receipt routes beneath existing view/update grants. Model-level
        // editable checks still apply, and receipts additionally belong to the caller.
        if ($this->db->getTableSchema('{{%auth_item}}', true) !== null) {
            foreach (['verse' => ['publication', 'operation'], 'meta' => ['operation']] as $controller => $actions) {
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
        foreach ([
            '{{%webmcp_operation}}' => ['id', 'actor_id', 'operation_id', 'target_type', 'target_id', 'action', 'request_hash', 'receipt', 'created_at'],
            '{{%scene_publication_revision}}' => ['id', 'revision', 'verse_id', 'snapshot_id', 'snapshot_uuid', 'source_revision', 'content_hash', 'snapshot_hash', 'snapshot_json', 'created_by', 'created_at'],
        ] as $table => $columns) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null || array_diff($columns, $schema->columnNames) !== []) {
                throw new \RuntimeException("Incomplete WebMCP schema: $table");
            }
        }
        foreach ([
            '{{%webmcp_operation}}' => ['uq_webmcp_actor_operation', ['actor_id', 'operation_id']],
            '{{%scene_publication_revision}}' => ['uq_scene_publication_revision', ['revision']],
        ] as $table => [$name, $columns]) {
            $matches = array_filter($this->db->schema->getTableIndexes($table, true), static fn ($index) =>
                $index->name === $name && $index->columnNames === $columns && $index->isUnique);
            if ($matches === []) {
                throw new \RuntimeException("Missing WebMCP unique constraint: $name");
            }
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
        echo "Receipt/archive history is intentionally retained. Roll back application code, not evidence.\n";
        return false;
    }
}
