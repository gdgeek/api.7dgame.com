<?php
namespace tests\integration\fixtures;

use yii\db\Connection;

/** Destructive fixture restricted to a disposable, loopback test database. */
final class AuthoringFixture
{
    public static function reset(Connection $db): void
    {
        WebMcpHttpFixture::assertTestDatabase($db);
        $tables = ['webmcp_operation', 'webmcp_authoring_task'];
        $schema = [
            'user' => 'id INT PRIMARY KEY AUTO_INCREMENT',
            'file' => 'id INT PRIMARY KEY AUTO_INCREMENT',
            'verse' => 'id INT PRIMARY KEY AUTO_INCREMENT, author_id INT, updater_id INT, name TEXT, uuid TEXT, data TEXT, info TEXT, description TEXT, image_id INT, created_at TEXT, updated_at TEXT',
            'meta' => 'id INT PRIMARY KEY AUTO_INCREMENT, author_id INT, updater_id INT, title TEXT, uuid TEXT, prefab INT DEFAULT 0, data TEXT, info TEXT, events TEXT, image_id INT, created_at TEXT, updated_at TEXT',
            'verse_code' => 'id INT PRIMARY KEY AUTO_INCREMENT, verse_id INT UNIQUE, code_id INT, blockly TEXT, js TEXT, lua TEXT',
            'meta_code' => 'id INT PRIMARY KEY AUTO_INCREMENT, meta_id INT UNIQUE, code_id INT, blockly TEXT, js TEXT, lua TEXT',
            'code' => 'id INT PRIMARY KEY AUTO_INCREMENT, js TEXT, lua TEXT',
            'verse_meta' => 'verse_id INT, meta_id INT',
            'meta_resource' => 'meta_id INT, resource_id INT',
            'resource' => 'id INT PRIMARY KEY AUTO_INCREMENT',
            'property' => 'id INT PRIMARY KEY AUTO_INCREMENT, `key` VARCHAR(255)',
            'verse_property' => 'verse_id INT, property_id INT',
            'group_verse' => 'group_id INT, verse_id INT',
            'group_user' => 'group_id INT, user_id INT',
            'manager' => 'id INT PRIMARY KEY AUTO_INCREMENT, verse_id INT, user_id INT',
            'verse_space' => 'id INT PRIMARY KEY AUTO_INCREMENT, verse_id INT, space_id INT',
            'space' => 'id INT PRIMARY KEY AUTO_INCREMENT, file_id INT, image_id INT, mesh_id INT, data TEXT',
            'snapshot' => 'id INT PRIMARY KEY AUTO_INCREMENT, verse_id INT, uuid TEXT, code TEXT, data TEXT, metas TEXT, resources TEXT, space TEXT, managers TEXT, created_at TEXT, created_by INT',
        ];
        foreach (array_merge($tables, array_keys($schema)) as $table) {
            $db->createCommand('DROP TABLE IF EXISTS ' . $db->quoteTableName($table))->execute();
        }
        foreach ($schema as $table => $columns) {
            $db->createCommand('CREATE TABLE ' . $db->quoteTableName($table) . " ($columns) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
        }
        $db->schema->refresh();
        $db->createCommand()->batchInsert('user', ['id'], [[7], [8]])->execute();
        foreach (['m260911_230000_add_webmcp_write_receipts', 'm260917_010000_add_webmcp_authoring_tasks'] as $class) {
            require_once dirname(__DIR__, 3) . '/console/migrations/' . $class . '.php';
            ob_start();
            try { (new $class(['db' => $db, 'compact' => true]))->safeUp(); } finally { ob_end_clean(); }
        }
    }
}
