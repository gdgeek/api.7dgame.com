<?php

use yii\db\Migration;

/**
 * Rebuilds the space data contract and restores verse-space bindings.
 */
class m260427_000000_rebuild_space_and_verse_space_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $spaceRows = $this->spaceRowsForNewContract();
        $verseSpaceRows = $this->verseSpaceRowsForNewContract();

        if ($this->db->schema->getTableSchema('{{%verse_space}}', true) !== null) {
            $this->dropTable('{{%verse_space}}');
        }

        if ($this->db->schema->getTableSchema('{{%space}}', true) !== null) {
            $this->dropTable('{{%space}}');
        }

        $this->createTable('{{%space}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'mesh_id' => $this->integer()->notNull(),
            'image_id' => $this->integer(),
            'file_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'data' => $this->json(),
        ], $tableOptions);

        if ($spaceRows !== []) {
            $this->batchInsert('{{%space}}', [
                'id',
                'name',
                'user_id',
                'mesh_id',
                'image_id',
                'file_id',
                'created_at',
                'data',
            ], $spaceRows);
        }

        $this->createIndex('{{%idx-space-user_id}}', '{{%space}}', 'user_id');
        $this->addForeignKey(
            '{{%fk-space-user_id}}',
            '{{%space}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-space-mesh_id}}', '{{%space}}', 'mesh_id');
        $this->addForeignKey(
            '{{%fk-space-mesh_id}}',
            '{{%space}}',
            'mesh_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-space-image_id}}', '{{%space}}', 'image_id');
        $this->addForeignKey(
            '{{%fk-space-image_id}}',
            '{{%space}}',
            'image_id',
            '{{%file}}',
            'id',
            'SET NULL'
        );

        $this->createIndex('{{%idx-space-file_id}}', '{{%space}}', 'file_id');
        $this->addForeignKey(
            '{{%fk-space-file_id}}',
            '{{%space}}',
            'file_id',
            '{{%file}}',
            'id',
            'SET NULL'
        );

        $this->createTable('{{%verse_space}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'space_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
        ], $tableOptions);

        if ($verseSpaceRows !== []) {
            $this->batchInsert('{{%verse_space}}', [
                'id',
                'verse_id',
                'space_id',
                'created_at',
            ], $verseSpaceRows);
        }

        $this->createIndex('{{%idx-verse_space-verse_id-unique}}', '{{%verse_space}}', 'verse_id', true);
        $this->addForeignKey(
            '{{%fk-verse_space-verse_id}}',
            '{{%verse_space}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-verse_space-space_id}}', '{{%verse_space}}', 'space_id');
        $this->addForeignKey(
            '{{%fk-verse_space-space_id}}',
            '{{%verse_space}}',
            'space_id',
            '{{%space}}',
            'id',
            'CASCADE'
        );
    }

    private function spaceRowsForNewContract(): array
    {
        $table = $this->db->schema->getTableSchema('{{%space}}', true);
        if ($table === null) {
            return [];
        }

        return array_map(
            fn (array $row): array => [
                (int) $row['id'],
                $this->firstNonEmpty($row, ['name', 'title'], 'Space ' . $row['id']),
                (int) ($row['user_id'] ?? $row['author_id'] ?? 0),
                (int) ($row['mesh_id'] ?? 0),
                $this->nullableInt($row['image_id'] ?? $row['sample_id'] ?? null),
                $this->nullableInt($row['file_id'] ?? $row['dat_id'] ?? null),
                $this->firstNonEmpty($row, ['created_at'], gmdate('Y-m-d H:i:s')),
                $row['data'] ?? $row['info'] ?? null,
            ],
            $this->db->createCommand('SELECT * FROM {{%space}}')->queryAll()
        );
    }

    private function verseSpaceRowsForNewContract(): array
    {
        $table = $this->db->schema->getTableSchema('{{%verse_space}}', true);
        if ($table === null) {
            return [];
        }

        return array_map(
            fn (array $row): array => [
                (int) $row['id'],
                (int) $row['verse_id'],
                (int) $row['space_id'],
                $this->firstNonEmpty($row, ['created_at'], gmdate('Y-m-d H:i:s')),
            ],
            $this->db->createCommand('SELECT * FROM {{%verse_space}}')->queryAll()
        );
    }

    private function firstNonEmpty(array $row, array $columns, string $fallback): string
    {
        foreach ($columns as $column) {
            if (isset($row[$column]) && trim((string) $row[$column]) !== '') {
                return (string) $row[$column];
            }
        }

        return $fallback;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%verse_space}}', true) !== null) {
            $this->dropTable('{{%verse_space}}');
        }

        if ($this->db->schema->getTableSchema('{{%space}}', true) !== null) {
            $this->dropTable('{{%space}}');
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%space}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'sample_id' => $this->integer()->notNull(),
            'mesh_id' => $this->integer()->notNull(),
            'dat_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
            'name' => $this->string(),
        ], $tableOptions);

        $this->createIndex('{{%idx-space-author_id}}', '{{%space}}', 'author_id');
        $this->addForeignKey('{{%fk-space-author_id}}', '{{%space}}', 'author_id', '{{%user}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-sample_id}}', '{{%space}}', 'sample_id');
        $this->addForeignKey('{{%fk-space-sample_id}}', '{{%space}}', 'sample_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-mesh_id}}', '{{%space}}', 'mesh_id');
        $this->addForeignKey('{{%fk-space-mesh_id}}', '{{%space}}', 'mesh_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-dat_id}}', '{{%space}}', 'dat_id');
        $this->addForeignKey('{{%fk-space-dat_id}}', '{{%space}}', 'dat_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-image_id}}', '{{%space}}', 'image_id');
        $this->addForeignKey('{{%fk-space-image_id}}', '{{%space}}', 'image_id', '{{%file}}', 'id', 'CASCADE');
    }
}
