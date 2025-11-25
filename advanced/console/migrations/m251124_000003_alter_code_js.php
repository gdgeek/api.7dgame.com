<?php

use yii\db\Migration;

class m251124_000003_alter_code_js extends Migration
{
    public function safeUp()
    {
        // 扩展 js 列为 MEDIUMTEXT 以容纳更大的脚本
        $this->execute("ALTER TABLE `code` MODIFY `js` MEDIUMTEXT NULL");
    }

    public function safeDown()
    {
        // 回退为 TEXT（可能导致数据截断）
        $this->execute("ALTER TABLE `code` MODIFY `js` TEXT NULL");
    }
}
