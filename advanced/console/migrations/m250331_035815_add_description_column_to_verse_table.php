<?php

use yii\db\Migration;

class m250331_035815_add_description_column_to_verse_table extends Migration
{
    public function safeUp()
    {
        // 1. 保存当前 SQL 模式并临时切换到宽松模式
        $this->execute("SET @OLD_SQL_MODE = @@SQL_MODE");
        $this->execute("SET SESSION SQL_MODE = ''");

        // 2. 首先将无效的日期值更新为 NULL
        $this->execute("UPDATE {{%verse}} SET updated_at = NULL WHERE updated_at = '0000-00-00 00:00:00'");

        // 3. 修改 updated_at 字段为 DATETIME 类型，允许为 NULL
        $this->alterColumn('{{%verse}}', 'updated_at', $this->dateTime()->null());

        // 4. 将 NULL 值更新为当前时间
        $this->execute("UPDATE {{%verse}} SET updated_at = NOW() WHERE updated_at IS NULL");

        // 5. 设置默认值为当前时间
        $this->alterColumn('{{%verse}}', 'updated_at', $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'));

        // 6. 添加 description 列
        $this->addColumn('{{%verse}}', 'description', $this->string(255));

        // 7. 恢复原始 SQL 模式
        $this->execute("SET SESSION SQL_MODE = @OLD_SQL_MODE");
    }

    public function safeDown()
    {
        // 1. 保存当前 SQL 模式并临时切换到宽松模式
        $this->execute("SET @OLD_SQL_MODE = @@SQL_MODE");
        $this->execute("SET SESSION SQL_MODE = ''");

        // 2. 移除 description 列
        $this->dropColumn('{{%verse}}', 'description');

        // 3. 恢复 updated_at 字段的原始设置
        $this->alterColumn('{{%verse}}', 'updated_at', $this->timestamp());

        // 4. 恢复原始 SQL 模式
        $this->execute("SET SESSION SQL_MODE = @OLD_SQL_MODE");
    }
}