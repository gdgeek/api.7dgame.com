<?php

use yii\db\Migration;

/**
 * 统一 JSON 字段类型迁移
 * 将所有 longtext + CHECK(json_valid) 的字段改为原生 JSON 类型
 *
 * 涉及表: ai_rodin, local, meta, resource, space, verse, vp_key_value, vp_map, user_info
 */
class m260211_190000_unify_json_columns extends Migration
{
    /**
     * 需要迁移的字段定义
     * 格式: [表名, 字段名, CHECK约束名]
     */
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
        // 第一步: 清理非法 JSON 数据（空字符串 → NULL）
        foreach ($this->columns as [$table, $column, $check]) {
            $this->update($table, [$column => null], ["$column" => '']);
        }

        // 第二步: 删除存在的 CHECK 约束
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

        // 第三步: 修改字段类型为 JSON
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` MODIFY `$column` JSON DEFAULT NULL");
        }
    }

    public function safeDown()
    {
        // 回滚: 改回 longtext
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute(
                "ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL"
            );
        }

        // 重新添加 CHECK 约束
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` ADD CONSTRAINT `$check` CHECK (JSON_VALID(`$column`))");
        }
    }
}
