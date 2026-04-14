<?php

use yii\db\Expression;
use yii\db\Migration;

class m260413_010300_seed_manage_organizations_permission extends Migration
{
    public $db = 'pluginDb';

    private bool $_skip = false;

    public function init()
    {
        if (!\Yii::$app->has('pluginDb')) {
            $this->_skip = true;
            $this->db = \Yii::$app->db;
            parent::init();
            return;
        }

        $this->db = 'pluginDb';
        parent::init();
    }

    public function safeUp()
    {
        if ($this->_skip) {
            echo "    > pluginDb not configured, skipping.\n";
            return;
        }

        foreach (['root', 'admin'] as $role) {
            $this->db->createCommand()->upsert('{{%plugin_permission_config}}', [
                'role_or_permission' => $role,
                'plugin_name' => 'system-admin',
                'action' => 'manage-organizations',
            ], [
                'updated_at' => new Expression('CURRENT_TIMESTAMP'),
            ])->execute();
        }
    }

    public function safeDown()
    {
        if ($this->_skip) {
            return;
        }

        $this->delete('{{%plugin_permission_config}}', [
            'plugin_name' => 'system-admin',
            'action' => 'manage-organizations',
        ]);
    }
}
