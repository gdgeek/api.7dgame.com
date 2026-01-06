<?php

use yii\db\Migration;

/**
 * Class m190528_142525_add_fk_user_id
 */
class m190528_142525_add_fk_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // This migration was erroneous as it tried to add a FK to table 'polygon' which didn't exist yet.
        // The table 'polygen' is created later in m190626_094428_create_polygen_table.php and handles the FK there.
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
