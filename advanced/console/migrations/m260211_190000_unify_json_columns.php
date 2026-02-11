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
        // ai_rodin: 4个字段
        ['ai_rodin', 'generation', 'ai_rodin_chk_1'],
        ['ai_rodin', 'check', 'ai_rodin_chk_2'],
        ['ai_rodin', 'download', 'ai_rodin_chk_3'],
        ['ai_rodin', 'query', 'ai_rodin_chk_4'],
        // local: 1个字段
        ['local', 'value', 'local_chk_1'],
        // meta: 3个字段
        ['meta', 'info', 'meta_chk_1'],
        ['meta', 'data', 'meta_chk_2'],
        ['meta', 'events', 'meta_chk_3'],
        // resource: 1个字段
        ['resource', 'info', 'resource_chk_1'],
        // space: 1个字段
        ['space', 'info', 'space_chk_1'],
        // verse: 2个字段
        ['verse', 'info', 'verse_chk_1'],
        ['verse', 'data', 'verse_chk_2'],
        // vp_key_value: 1个字段
        ['vp_key_value', 'value', 'vp_key_value_chk_1'],
        // vp_map: 1个字段
        ['vp_map', 'info', 'vp_map_chk_1'],
        // user_info: 1个字段
        ['user_info', 'info', 'user_info_chk_1'],
    ];

    public function safeUp()
    {
        // 第一步: 清理非法 JSON 数据（空字符串 → NULL）
        foreach ($this->columns as [$table, $column, $check]) {
            $this->update($table, [$column => null], ["$column" => '']);
        }

        // 第二步: 删除 CHECK 约束
        // 按表分组，避免重复 ALTER
        $checksByTable = [];
        foreach ($this->columns as [$table, $column, $check]) {
            $checksByTable[$table][] = $check;
        }
        foreach ($checksByTable as $table => $checks) {
            $dropParts = implode(', ', array_map(fn($c) => "DROP CHECK `$c`", $checks));
            $this->execute("ALTER TABLE `$table` $dropParts");
        }

        // 第三步: 修改字段类型为 JSON
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` MODIFY `$column` JSON DEFAULT NULL");
        }
    }

    public function safeDown()
    {
        // 回滚: 改回 longtext 并重新添加 CHECK 约束
        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute(
                "ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL"
            );
        }

        foreach ($this->columns as [$table, $column, $check]) {
            $this->execute("ALTER TABLE `$table` ADD CONSTRAINT `$check` CHECK (JSON_VALID(`$column`))");
        }
    }
}
