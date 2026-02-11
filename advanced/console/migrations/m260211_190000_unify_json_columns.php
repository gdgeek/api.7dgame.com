<?php

use yii\db\Migration;

/**
 * 统一 JSON 字段类型迁移
 * 将所有 longtext + CHECK(json_valid) 的字段改为原生 JSON 类型
 */
class m260211_190000_unify_json_columns extends Migration
{
    private array $columns = [
        ['ai_rodin', 'generation', 'ai_rodin_chk_1'],
        ['ai_rodin', 'check', 'ai_rodin_chk_2'],
        ['ai_rodin', 'download', 'ai_rodin_chk_3'],
        ['ai_rodin', 'query', 'ai_rodin_chk_4'],
        ['local', 'value', 'local_chk_1'],
        ['meta', 'info', 'meta_chk_1'],
        ['meta', 'data', 'meta_chk_2'],
        ['meta', 'events', 'meta_chk_3'],
        ['resource', 'info', 'resource_chk_1'],
        ['space', 'info', 'space_chk_1'],
        ['verse', 'info', 'verse_chk_1'],
        ['verse', 'data', 'verse_chk_2'],
        ['vp_key_value', 'value', 'vp_key_value_chk_1'],
        ['vp_map', 'info', 'vp_map_chk_1'],
        ['user_info', 'info', 'user_info_chk_1'],
    ];

    public function safeUp()
    {
        // 第一步: 修复含有非法默认值 '0000-00-00 00:00:00' 的 timestamp 列
        // ALTER TABLE MODIFY 某列时 MySQL 严格模式会校验整张表所有列
        $this->fixInvalidTimestampDefaults();

        // 第二步: 清理非法 JSON 数据（空字符串 → NULL）
        foreach ($this->columns as [$table, $column, $check]) {
            $this->update($table, [$column => null], ["$column" => '']);
        }

        // 第三步: 删除存在的 CHECK 约束
        $dbName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();
        foreach ($this->columns as [$table, $column, $check]) {
            $exists = $this->db->createCommand(
                "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS " .
                "WHERE CONSTRAINT_SCHEMA = :db AND TABLE_NAME = :table " .
                "AND CONSTRAINT_NAME = :check AND CONSTRAINT_TYPE = 'CHECK'",
                [':db' => $dbName, ':table' => $table, ':check' => $check]
            )->queryScalar();

            if ($exists) {
                $this->execute("ALTER TABLE `$table` DROP CHECK `$check`");
            }
        }

        // 第四步: 修改字段类型为 JSON
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` MODIFY `$column` JSON DEFAULT NULL");
        }
    }

    public function safeDown()
    {
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute(
                "ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL"
            );
        }

        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` ADD CONSTRAINT `$check` CHECK (JSON_VALID(`$column`))");
        }
    }

    /**
     * 查找并修复所有涉及表中 '0000-00-00 00:00:00' 默认值的 timestamp 列
     */
    private function fixInvalidTimestampDefaults(): void
    {
        // 收集所有涉及的表名
        $tables = array_unique(array_column($this->columns, 0));
        $dbName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();

        foreach ($tables as $table) {
            $badColumns = $this->db->createCommand(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS " .
                "WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table " .
                "AND DATA_TYPE = 'timestamp' AND COLUMN_DEFAULT = '0000-00-00 00:00:00'",
                [':db' => $dbName, ':table' => $table]
            )->queryColumn();

            foreach ($badColumns as $col) {
                $this->execute(
                    "ALTER TABLE `$table` MODIFY `$col` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
                );
            }
        }
    }
}
