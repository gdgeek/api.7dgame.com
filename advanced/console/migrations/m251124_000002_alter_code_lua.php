<?php

use yii\db\Migration;

class m251124_000002_alter_code_lua extends Migration
{
    public function safeUp()
    {
        // 将 lua 列扩展为 MEDIUMTEXT 以容纳更大的脚本文本
        $this->execute("ALTER TABLE `code` MODIFY `lua` MEDIUMTEXT NULL");
    }

    public function safeDown()
    {
        // 回退为 TEXT（可能导致数据截断）
        $this->execute("ALTER TABLE `code` MODIFY `lua` TEXT NULL");
    }
}
