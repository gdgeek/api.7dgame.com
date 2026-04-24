<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%version}}`.
 */
class m251223_094247_create_version_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $table = $this->db->schema->getTableSchema('{{%version}}', true);
        if ($table !== null) {
            if (!isset($table->columns['name'])) {
                $this->addColumn('{{%version}}', 'name', $this->string());
            }
            if (!isset($table->columns['number'])) {
                $this->addColumn('{{%version}}', 'number', $this->integer());
            }
            if (!isset($table->columns['created_at'])) {
                $this->addColumn('{{%version}}', 'created_at', $this->datetime());
            }
            if (!isset($table->columns['info'])) {
                $this->addColumn('{{%version}}', 'info', $this->json());
            }

            $table = $this->db->schema->getTableSchema('{{%version}}', true);
            if (isset($table->columns['version'])) {
                $this->update(
                    '{{%version}}',
                    ['number' => new \yii\db\Expression($this->db->quoteColumnName('version'))],
                    ['and', ['number' => null], ['not', ['version' => null]]]
                );
            }
            $this->update(
                '{{%version}}',
                ['number' => new \yii\db\Expression($this->db->quoteColumnName('id'))],
                ['number' => null]
            );

            $rows = (new \yii\db\Query())
                ->select(['id', 'number'])
                ->from('{{%version}}')
                ->where(['or', ['name' => null], ['created_at' => null]])
                ->all($this->db);
            foreach ($rows as $row) {
                $this->update('{{%version}}', [
                    'name' => 'Version ' . $row['number'],
                    'created_at' => date('Y-m-d H:i:s'),
                ], ['id' => $row['id']]);
            }

            $this->alterColumn('{{%version}}', 'name', $this->string()->notNull());
            $this->alterColumn('{{%version}}', 'number', $this->integer()->notNull());
            $this->alterColumn('{{%version}}', 'created_at', $this->datetime()->notNull());

            $table = $this->db->schema->getTableSchema('{{%version}}', true);
            if ($this->uniqueIndexNameForColumns($table, ['number']) === null) {
                $this->createIndex('{{%idx-version-number}}', '{{%version}}', 'number', true);
            }

            return;
        }

        $this->createTable('{{%version}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'number' => $this->integer()->notNull()->unique(),
            'created_at' => $this->datetime()->notNull(),
            'info' => $this->json(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%version}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['version'])) {
            $indexName = $this->uniqueIndexNameForColumns($table, ['number']);
            if ($indexName !== null) {
                $this->dropIndex($indexName, '{{%version}}');
            }

            foreach (['info', 'created_at', 'number', 'name'] as $column) {
                $table = $this->db->schema->getTableSchema('{{%version}}', true);
                if (isset($table->columns[$column])) {
                    $this->dropColumn('{{%version}}', $column);
                }
            }

            return;
        }

        $this->dropTable('{{%version}}');
    }

    private function uniqueIndexNameForColumns($table, array $columns)
    {
        foreach ($this->db->schema->findUniqueIndexes($table) as $indexName => $indexColumns) {
            if ($indexColumns === $columns) {
                return $indexName;
            }
        }

        return null;
    }
}
