<?php

use yii\db\Migration;

class m261118_000002_create_resource_subtitle extends Migration
{
    public function safeUp()
    {
        $existing = $this->db->schema->getTableSchema('{{%resource_subtitle}}', true);
        if ($existing !== null) {
            $resource = $this->db->tablePrefix . 'resource';
            if ($existing->primaryKey !== ['media_resource_id']
                || !in_array([0 => $resource, 'media_resource_id' => 'id'], $existing->foreignKeys, true)
                || !in_array([0 => $resource, 'subtitle_resource_id' => 'id'], $existing->foreignKeys, true)) {
                throw new \RuntimeException('Existing resource_subtitle schema does not match the migration.');
            }
            return;
        }

        $this->createTable('{{%resource_subtitle}}', [
            'media_resource_id' => $this->integer()->notNull(),
            'subtitle_resource_id' => $this->integer()->notNull(),
            'PRIMARY KEY ([[media_resource_id]])',
            'CONSTRAINT [[fk_resource_subtitle_media]] FOREIGN KEY ([[media_resource_id]]) REFERENCES {{%resource}} ([[id]]) ON DELETE CASCADE',
            'CONSTRAINT [[fk_resource_subtitle_asset]] FOREIGN KEY ([[subtitle_resource_id]]) REFERENCES {{%resource}} ([[id]]) ON DELETE CASCADE',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%resource_subtitle}}');
    }
}
