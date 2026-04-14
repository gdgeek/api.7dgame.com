<?php

use common\components\PluginRegistryUrlResolver;
use yii\db\Migration;

class m260413_000500_sync_plugin_registry_urls_from_env extends Migration
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

        if ($this->db->schema->getTableSchema('{{%plugins}}') === null) {
            echo "    > plugins table not found, skipping.\n";
            return;
        }

        foreach (['user-management', 'system-admin'] as $pluginId) {
            $resolved = PluginRegistryUrlResolver::forPlugin($pluginId);
            $this->update('{{%plugins}}', $resolved, ['id' => $pluginId]);
        }
    }

    public function safeDown()
    {
        if ($this->_skip) {
            return;
        }

        if ($this->db->schema->getTableSchema('{{%plugins}}') === null) {
            return;
        }

        foreach (['user-management', 'system-admin'] as $pluginId) {
            $defaults = PluginRegistryUrlResolver::productionDefaults($pluginId);
            $this->update('{{%plugins}}', $defaults, ['id' => $pluginId]);
        }
    }
}
