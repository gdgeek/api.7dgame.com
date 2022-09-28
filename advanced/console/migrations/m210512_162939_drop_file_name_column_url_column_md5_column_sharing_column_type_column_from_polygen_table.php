<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%polygen}}`.
 */
class m210512_162939_drop_file_name_column_url_column_md5_column_sharing_column_type_column_from_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%polygen}}', 'file_name');
        $this->dropColumn('{{%polygen}}', 'url');
        $this->dropColumn('{{%polygen}}', 'md5');
        $this->dropColumn('{{%polygen}}', 'sharing');
        $this->dropColumn('{{%polygen}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%polygen}}', 'file_name', $this->string());
        $this->addColumn('{{%polygen}}', 'url', $this->string());
        $this->addColumn('{{%polygen}}', 'md5', $this->string());
        $this->addColumn('{{%polygen}}', 'sharing', $this->integer());
        $this->addColumn('{{%polygen}}', 'type', $this->string());
    }
}
