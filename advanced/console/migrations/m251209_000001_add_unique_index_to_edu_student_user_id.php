<?php

use yii\db\Migration;

/**
 * Add unique index to user_id column in edu_student table
 */
class m251209_000001_add_unique_index_to_edu_student_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add unique index to user_id column
        $this->createIndex(
            'idx-edu_student-user_id-unique',
            'edu_student',
            'user_id',
            true  // unique
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the unique index
        $this->dropIndex(
            'idx-edu_student-user_id-unique',
            'edu_student'
        );
    }
}
