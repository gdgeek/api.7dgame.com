<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_code}}`.
 */
class m260105_145031_add_lua_column_js_column_to_verse_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_code}}', 'lua', "LONGTEXT");
        $this->addColumn('{{%verse_code}}', 'js', "LONGTEXT");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_code}}', 'lua');
        $this->dropColumn('{{%verse_code}}', 'js');
    }
}
