<?php

use yii\db\Migration;
use yii\db\Expression;

/**
 * Allows managed/plugin users to omit email addresses.
 */
class m260422_000002_allow_null_user_email extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null || !isset($table->columns['email'])) {
            return;
        }

        $this->alterColumn('{{%user}}', 'email', $this->string()->null());
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null || !isset($table->columns['email'])) {
            return;
        }

        $this->update(
            '{{%user}}',
            ['email' => new Expression("CONCAT('user+', " . $this->db->quoteColumnName('id') . ", '@invalid.local')")],
            ['email' => null]
        );
        $this->alterColumn('{{%user}}', 'email', $this->string()->notNull());
    }
}
