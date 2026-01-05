<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta_code}}`.
 */
class m260105_144929_add_lua_column_js_column_to_meta_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta_code}}', 'lua', "LONGTEXT");
        $this->addColumn('{{%meta_code}}', 'js', "LONGTEXT");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta_code}}', 'lua');
        $this->dropColumn('{{%meta_code}}', 'js');
    }
}
