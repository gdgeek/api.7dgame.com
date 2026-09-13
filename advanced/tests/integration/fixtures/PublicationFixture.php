<?php
namespace tests\integration\fixtures;
require_once __DIR__ . '/WebMcpHttpFixture.php';
final class PublicationFixture
{
    private const TABLES = ['scene_publication_revision','webmcp_operation','snapshot','verse','verse_code','meta','meta_code','code','verse_meta','meta_resource','resource','file','manager','verse_space','space','user'];
    public static function reset(\yii\db\Connection $db): void
    {
        $config = WebMcpHttpFixture::databaseConfig();
        if ($config['dsn'] !== $db->dsn) throw new \RuntimeException('Fixture DSN mismatch');
        WebMcpHttpFixture::assertTestDatabase($db);
        foreach (self::TABLES as $table) $db->createCommand("DROP TABLE IF EXISTS $table")->execute();
        foreach ([
            'user'=>'id INT PRIMARY KEY',
            'verse'=>'id INT PRIMARY KEY, author_id INT, updater_id INT, name TEXT, uuid TEXT, data JSON, description TEXT, image_id INT',
            'verse_code'=>'id INT PRIMARY KEY, verse_id INT, code_id INT, lua TEXT, js TEXT, blockly TEXT',
            'meta'=>'id INT PRIMARY KEY, author_id INT, title TEXT, uuid TEXT, prefab INT DEFAULT 0, data JSON, events JSON',
            'meta_code'=>'id INT PRIMARY KEY, meta_id INT, code_id INT, lua TEXT, js TEXT, blockly TEXT',
            'code'=>'id INT PRIMARY KEY, lua TEXT, js TEXT',
            'verse_meta'=>'verse_id INT, meta_id INT', 'meta_resource'=>'meta_id INT, resource_id INT',
            'resource'=>'id INT PRIMARY KEY, name TEXT, uuid TEXT, type TEXT, info JSON, file_id INT, image_id INT, created_at TEXT',
            'file'=>'id INT PRIMARY KEY, md5 TEXT, type TEXT, url TEXT, filename TEXT, size INT, `key` TEXT',
            'manager'=>'id INT PRIMARY KEY, verse_id INT, type TEXT, data JSON',
            'verse_space'=>'id INT PRIMARY KEY, verse_id INT, space_id INT',
            'space'=>'id INT PRIMARY KEY, file_id INT, image_id INT, mesh_id INT, data JSON',
            'snapshot'=>'id INT PRIMARY KEY AUTO_INCREMENT, verse_id INT, uuid TEXT, code TEXT, data LONGTEXT, metas JSON, resources JSON, space JSON, managers JSON, created_at DATETIME, created_by INT',
        ] as $name=>$fields) $db->createCommand("CREATE TABLE $name ($fields) ENGINE=InnoDB")->execute();
        foreach (['m260911_230000_add_webmcp_write_receipts','m260913_120000_add_scene_publication_history'] as $class) {
            require_once dirname(__DIR__,3).'/console/migrations/'.$class.'.php';
            ob_start(); (new $class(['db'=>$db,'compact'=>true]))->safeUp(); ob_end_clean();
        }
        $db->createCommand()->insert('user',['id'=>7])->execute();
        $db->createCommand()->insert('verse',['id'=>1,'author_id'=>7,'name'=>'A','uuid'=>'scene','data'=>'{"children":{"modules":[{"parameters":{"meta_id":2}}]}}'])->execute();
        $db->createCommand()->insert('meta',['id'=>2,'title'=>'old','uuid'=>'entity','data'=>'{}','events'=>'{}'])->execute();
        $db->createCommand()->insert('verse_meta',['verse_id'=>1,'meta_id'=>2])->execute();
        $db->createCommand()->insert('verse_code',['id'=>1,'verse_id'=>1,'lua'=>'old scene','js'=>'old scene js'])->execute();
        $db->createCommand()->insert('meta_code',['id'=>2,'meta_id'=>2,'lua'=>'old meta','js'=>'old meta js'])->execute();
        $db->createCommand()->insert('file',['id'=>3,'url'=>'https://example.com/a?signature=ephemeral','key'=>'stable/a','filename'=>'a','size'=>5,'md5'=>'known','type'=>'image/png'])->execute();
        $db->createCommand()->insert('resource',['id'=>4,'name'=>'old resource','uuid'=>null,'file_id'=>3,'type'=>'image','info'=>'{}'])->execute();
        $db->createCommand()->insert('meta_resource',['meta_id'=>2,'resource_id'=>4])->execute();
    }
}
