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
        ] as $table => $columns) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null || array_diff($columns, $schema->columnNames) !== []) {
                throw new \RuntimeException("Incomplete WebMCP schema: $table");
            }
        }
        foreach ([
            '{{%webmcp_operation}}' => ['uq_webmcp_actor_operation', ['actor_id', 'operation_id']],
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
        echo "Operation receipts are intentionally retained. Roll back application code, not evidence.\n";
        return false;
    }
}
