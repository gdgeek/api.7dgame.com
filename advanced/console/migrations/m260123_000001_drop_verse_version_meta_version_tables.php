<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `verse_version` and `meta_version`.
 */
class m260123_000001_drop_verse_version_meta_version_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('verse_version');
        $this->dropTable('meta_version');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('meta_version', [
            'id' => $this->primaryKey(),
            'meta_id' => $this->integer()->notNull(),
            'version_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-meta_version-meta_id',
            'meta_version',
            'meta_id',
            'meta',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-meta_version-version_id',
            'meta_version',
            'version_id',
            'version',
            'id',
            'CASCADE'
        );

        $this->createTable('verse_version', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'version_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-verse_version-verse_id',
            'verse_version',
            'verse_id',
            'verse',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-verse_version-version_id',
            'verse_version',
            'version_id',
            'version',
            'id',
            'CASCADE'
        );
    }
}
