<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rete}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m211217_232656_create_meta_rete_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    { 
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%meta_rete}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'meta_id' => $this->integer(),
            'data' => $this->json(),
        ], $tableOptions);

       // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_rete-meta_id}}',
            '{{%meta_rete}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-meta_rete-meta_id}}',
            '{{%meta_rete}}',
            'meta_id',
            '{{%meta}}',
            'id',
            'CASCADE'
        );


      
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-meta_rete-meta_id}}',
            '{{%meta_rete}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-meta_rete-meta_id}}',
            '{{%meta_rete}}'
        );


        $this->dropTable('{{%meta_rete}}');
    }
}
