<?php

use yii\db\Migration;

/**
 * 修复所有表中 timestamp 列的非法默认值 '0000-00-00 00:00:00'
 * MySQL 严格模式下该默认值会导致 ALTER TABLE 报错
 */
class m260211_200000_fix_invalid_timestamp_defaults extends Migration
{
    public function safeUp()
    {
        $dbName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();

        $badColumns = $this->db->createCommand(
            "SELECT TABLE_NAME, COLUMN_NAME, IS_NULLABLE FROM information_schema.COLUMNS " .
            "WHERE TABLE_SCHEMA = :db " .
            "AND DATA_TYPE = 'timestamp' " .
            "AND COLUMN_DEFAULT = '0000-00-00 00:00:00'",
            [':db' => $dbName]
        )->queryAll();

        foreach ($badColumns as $row) {
            $table = $row['TABLE_NAME'];
            $col = $row['COLUMN_NAME'];
            $nullable = $row['IS_NULLABLE'] === 'YES';

            if ($nullable) {
                $this->execute("ALTER TABLE `$table` MODIFY `$col` TIMESTAMP NULL DEFAULT NULL");
            } else {
                $this->execute("ALTER TABLE `$table` MODIFY `$col` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
            }
        }
    }

    public function safeDown()
    {
        // 不可逆：不应恢复非法默认值
        return true;
    }
}
