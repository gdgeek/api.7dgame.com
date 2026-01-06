<?php

use yii\db\Migration;

class m251124_000001_alter_meta_code_blockly extends Migration
{
    public function safeUp()
    {
        // 将 blockly 改为 MEDIUMTEXT（可存储更大的数据）
        $this->execute("ALTER TABLE `meta_code` MODIFY `blockly` MEDIUMTEXT NULL");
    }

    public function safeDown()
    {
        // 回退为 TEXT（注意：回退可能会导致数据截断）
        $this->execute("ALTER TABLE `meta_code` MODIFY `blockly` TEXT NULL");
    }
}
