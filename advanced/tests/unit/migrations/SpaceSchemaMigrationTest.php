<?php

namespace tests\unit\migrations;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

final class SpaceSchemaMigrationTest extends TestCase
{
    private $originalDbComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();

        Yii::$app->db->createCommand()->createTable('user', [
            'id' => 'pk',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('file', [
            'id' => 'pk',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('verse', [
            'id' => 'pk',
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testMigrationReplacesLegacySpaceTablesWithNewContract(): void
    {
        $migrationPath = dirname(__DIR__, 3)
            . '/console/migrations/m260427_000000_rebuild_space_and_verse_space_tables.php';
        $this->assertFileExists($migrationPath);
        require_once $migrationPath;

        Yii::$app->db->createCommand()->createTable('space', [
            'id' => 'pk',
            'title' => 'string not null',
            'author_id' => 'integer not null',
            'sample_id' => 'integer not null',
            'mesh_id' => 'integer not null',
            'dat_id' => 'integer not null',
            'info' => 'text',
            'created_at' => 'datetime',
            'image_id' => 'integer',
            'name' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('verse_space', [
            'id' => 'pk',
            'verse_id' => 'integer not null',
            'space_id' => 'integer not null',
            'created_at' => 'datetime',
        ])->execute();
        Yii::$app->db->createCommand()->insert('space', [
            'id' => 7,
            'title' => 'Legacy Space',
            'author_id' => 42,
            'sample_id' => 101,
            'mesh_id' => 102,
            'dat_id' => 103,
            'info' => '{"source":"legacy-space"}',
            'created_at' => '2026-04-27 12:00:00',
            'image_id' => null,
            'name' => null,
        ])->execute();
        Yii::$app->db->createCommand()->insert('verse_space', [
            'id' => 9,
            'verse_id' => 3,
            'space_id' => 7,
            'created_at' => '2026-04-27 12:30:00',
        ])->execute();

        $migration = new class extends \m260427_000000_rebuild_space_and_verse_space_tables {
            public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
            {
            }

            public function dropForeignKey($name, $table)
            {
            }
        };
        $migration->safeUp();

        $space = Yii::$app->db->schema->getTableSchema('space', true);
        $this->assertSame(
            ['id', 'name', 'user_id', 'mesh_id', 'image_id', 'file_id', 'created_at', 'data'],
            array_keys($space->columns)
        );
        $this->assertArrayNotHasKey('title', $space->columns);
        $this->assertArrayNotHasKey('author_id', $space->columns);
        $this->assertArrayNotHasKey('sample_id', $space->columns);
        $this->assertArrayNotHasKey('dat_id', $space->columns);
        $this->assertArrayNotHasKey('info', $space->columns);
        $migratedSpace = Yii::$app->db->createCommand('SELECT * FROM space WHERE id = 7')->queryOne();
        foreach (['id', 'user_id', 'mesh_id', 'image_id', 'file_id'] as $column) {
            $migratedSpace[$column] = (int)$migratedSpace[$column];
        }
        $this->assertSame([
            'id' => 7,
            'name' => 'Legacy Space',
            'user_id' => 42,
            'mesh_id' => 102,
            'image_id' => 101,
            'file_id' => 103,
            'created_at' => '2026-04-27 12:00:00',
            'data' => '{"source":"legacy-space"}',
        ], $migratedSpace);

        $verseSpace = Yii::$app->db->schema->getTableSchema('verse_space', true);
        $this->assertSame(
            ['id', 'verse_id', 'space_id', 'created_at'],
            array_keys($verseSpace->columns)
        );
        $migratedVerseSpace = Yii::$app->db->createCommand('SELECT * FROM verse_space WHERE id = 9')->queryOne();
        foreach (['id', 'verse_id', 'space_id'] as $column) {
            $migratedVerseSpace[$column] = (int)$migratedVerseSpace[$column];
        }
        $this->assertSame([
            'id' => 9,
            'verse_id' => 3,
            'space_id' => 7,
            'created_at' => '2026-04-27 12:30:00',
        ], $migratedVerseSpace);

        $indexes = Yii::$app->db
            ->createCommand("PRAGMA index_list('verse_space')")
            ->queryAll();
        $uniqueIndexNames = array_column(
            array_filter($indexes, static fn (array $index): bool => (int) $index['unique'] === 1),
            'name'
        );
        $this->assertContains('idx-verse_space-verse_id-unique', $uniqueIndexNames);
    }

    public function testFileIdMigrationAddsRuntimePackageReferenceToExistingSpaceTable(): void
    {
        $migrationPath = dirname(__DIR__, 3)
            . '/console/migrations/m260427_020000_add_file_id_to_space_table.php';
        $this->assertFileExists($migrationPath);
        require_once $migrationPath;

        Yii::$app->db->createCommand()->createTable('space', [
            'id' => 'pk',
            'name' => 'string not null',
            'user_id' => 'integer not null',
            'mesh_id' => 'integer not null',
            'image_id' => 'integer',
            'created_at' => 'datetime',
            'data' => 'text',
        ])->execute();

        $migration = new class extends \m260427_020000_add_file_id_to_space_table {
            public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
            {
            }

            public function dropForeignKey($name, $table)
            {
            }
        };
        $migration->safeUp();

        $space = Yii::$app->db->schema->getTableSchema('space', true);
        $this->assertArrayHasKey('file_id', $space->columns);
    }
}
