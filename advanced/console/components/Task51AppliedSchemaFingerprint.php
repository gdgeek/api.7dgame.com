<?php

namespace console\components;

use JsonException;
use RuntimeException;
use Throwable;
use yii\db\Command;
use yii\db\Connection;
use yii\db\Transaction;

/**
 * Fail-closed, read-only verifier for the already-applied Task 5.1 schema.
 *
 * The verifier intentionally reads MySQL 8 data-dictionary views instead of
 * comparing SHOW CREATE output, whose presentation changes between releases.
 */
final class Task51AppliedSchemaFingerprint
{
    public const FORMAT = 'wp3-task51-applied-schema-fingerprint-v1';
    public const EXECUTION_TABLE = '{{%task51_stage_b_execution}}';
    public const TRANSITION_TABLE = '{{%task51_stage_b_transition}}';

    private const LOCK_WAIT_SECONDS = 20;
    private const MAX_METADATA_ROWS = 128;
    private const MAX_EXPRESSION_BYTES = 16384;

    /**
     * Shared database guard for both the exact migration and its no-op verifier.
     */
    public static function assertSupportedDatabase(Connection $db): void
    {
        if (get_class($db) !== Connection::class
            || $db->commandClass !== Command::class
            || $db->enableSlaves !== false) {
            throw new RuntimeException(
                'Task 5.1 migration requires the exact standard task51CoordinatorDb connection without retries or slaves.'
            );
        }
        if ($db->driverName !== 'mysql') {
            throw new RuntimeException('Task 5.1 coordinator requires Oracle MySQL 8.0.19 or newer with InnoDB.');
        }

        $serverVersion = (string)$db->getServerVersion();
        $versionComment = (string)$db->createCommand('SELECT @@version_comment')->queryScalar();
        if (stripos($serverVersion, 'mariadb') !== false
            || stripos($versionComment, 'mysql') === false
            || stripos($versionComment, 'percona') !== false
            || preg_match('/^(\d+\.\d+\.\d+)/D', $serverVersion, $matches) !== 1
            || version_compare($matches[1], '8.0.19', '<')) {
            throw new RuntimeException(
                'Task 5.1 coordinator requires Oracle MySQL 8.0.19 or newer with enforced CHECK constraints.'
            );
        }

        $db->createCommand(
            'SET SESSION lock_wait_timeout = ' . self::LOCK_WAIT_SECONDS
        )->execute();
        $db->createCommand(
            'SET SESSION innodb_lock_wait_timeout = ' . self::LOCK_WAIT_SECONDS
        )->execute();
    }

    /** Fail before migration DDL if the shared history authority cannot row-lock. */
    public function assertMigrationHistoryTable(
        Connection $db,
        string $migrationTable
    ): void {
        self::assertSupportedDatabase($db);
        if ($db->getTransaction() !== null) {
            throw new RuntimeException(
                'Task 5.1 migration-history inspection requires ownership of a fresh transaction.'
            );
        }

        $names = $this->resolveNames($db, $migrationTable);
        $transaction = $db->beginTransaction(Transaction::REPEATABLE_READ);
        try {
            $this->assertMigrationHistoryAuthority($db, $names['migration']);
            $transaction->commit();
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            if ($exception instanceof RuntimeException
                && str_starts_with($exception->getMessage(), 'Task 5.1')) {
                throw $exception;
            }
            throw new RuntimeException(
                'Task 5.1 migration-history inspection failed closed.',
                0,
                $exception
            );
        }
    }

    /**
     * Verify the exact history row and the complete live schema under metadata locks.
     *
     * @return non-empty-string SHA-256 of the canonical verified manifest
     */
    public function assertAlreadyApplied(
        Connection $db,
        string $migrationTable,
        string $exactMigration
    ): string {
        self::assertSupportedDatabase($db);
        if ($db->getTransaction() !== null) {
            throw new RuntimeException(
                'Task 5.1 applied-schema inspection requires ownership of a fresh transaction.'
            );
        }

        $names = $this->resolveNames($db, $migrationTable);
        $transaction = $db->beginTransaction(Transaction::REPEATABLE_READ);
        try {
            $this->assertMigrationHistoryAuthority($db, $names['migration']);
            $this->assertExactHistoryRow($db, $names['migration'], $exactMigration);

            // Referencing both tables in this explicit transaction holds their
            // metadata locks until commit/rollback. This prevents concurrent
            // DDL from producing a hybrid information_schema snapshot.
            $lockOrder = [$names['execution'], $names['transition']];
            sort($lockOrder, SORT_STRING);
            foreach ($lockOrder as $tableName) {
                $db->createCommand(
                    'SELECT 1 FROM ' . $db->quoteTableName($tableName) . ' LIMIT 0'
                )->queryAll();
            }

            $actual = $this->readActualManifest($db, $names);
            $expected = $this->expectedManifest($names);
            $actualJson = $this->canonicalJson($actual);
            $expectedJson = $this->canonicalJson($expected);
            if (!hash_equals(hash('sha256', $expectedJson), hash('sha256', $actualJson))
                || !hash_equals($expectedJson, $actualJson)) {
                throw new RuntimeException(
                    'Task 5.1 ALREADY_APPLIED schema fingerprint mismatch; refusing to continue.'
                );
            }

            $sha256 = hash('sha256', $actualJson);
            $transaction->commit();
            return $sha256;
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            if ($exception instanceof RuntimeException
                && str_starts_with($exception->getMessage(), 'Task 5.1')) {
                throw $exception;
            }
            throw new RuntimeException(
                'Task 5.1 applied-schema inspection failed closed.',
                0,
                $exception
            );
        }
    }

    /**
     * Public for deterministic parser unit tests; production callers use the
     * enclosing schema verifier.
     */
    public static function canonicalizeRestrictedExpression(string $expression): string
    {
        if ($expression === '' || strlen($expression) > self::MAX_EXPRESSION_BYTES) {
            throw new RuntimeException('Task 5.1 CHECK expression size is invalid.');
        }
        $parser = new Task51RestrictedSqlParser($expression);
        return self::canonicalJsonStatic($parser->parseBooleanExpression());
    }

    /** Public for deterministic trigger parser unit tests. */
    public static function canonicalizeTriggerAction(string $statement): string
    {
        if ($statement === '' || strlen($statement) > self::MAX_EXPRESSION_BYTES) {
            throw new RuntimeException('Task 5.1 trigger action size is invalid.');
        }
        $parser = new Task51RestrictedSqlParser($statement);
        return self::canonicalJsonStatic($parser->parseSignalAction());
    }

    /**
     * @return array{migration:string,execution:string,transition:string,prefix:string,database:string}
     */
    private function resolveNames(Connection $db, string $migrationTable): array
    {
        if ($migrationTable !== '{{%migration}}') {
            throw new RuntimeException('Task 5.1 migration-history override is forbidden.');
        }
        $prefix = $db->tablePrefix;
        if (!is_string($prefix) || preg_match('/\A[a-z0-9_]*\z/D', $prefix) !== 1) {
            throw new RuntimeException('Task 5.1 table prefix must be lowercase ASCII.');
        }

        $names = [
            'migration' => $db->schema->getRawTableName($migrationTable),
            'execution' => $db->schema->getRawTableName(self::EXECUTION_TABLE),
            'transition' => $db->schema->getRawTableName(self::TRANSITION_TABLE),
        ];
        $expected = [
            'migration' => $prefix . 'migration',
            'execution' => $prefix . 'task51_stage_b_execution',
            'transition' => $prefix . 'task51_stage_b_transition',
        ];
        foreach ($names as $role => $name) {
            if (!is_string($name)
                || $name !== $expected[$role]
                || strlen($name) > 64
                || preg_match('/\A[a-z0-9_]+\z/D', $name) !== 1) {
                throw new RuntimeException('Task 5.1 resolved table name is unsafe.');
            }
        }

        $database = (string)$db->createCommand('SELECT DATABASE()')->queryScalar();
        if ($database === '' || strlen($database) > 64 || str_contains($database, "\0")) {
            throw new RuntimeException('Task 5.1 current database authority is unavailable.');
        }

        return $names + ['prefix' => $prefix, 'database' => $database];
    }

    private function assertMigrationHistoryAuthority(Connection $db, string $migrationTable): void
    {
        // Acquire an MDL on the history table for the rest of this transaction
        // before trusting its engine metadata or its exact row.
        $db->createCommand(
            'SELECT 1 FROM ' . $db->quoteTableName($migrationTable) . ' LIMIT 0'
        )->queryAll();
        $rows = $db->createCommand(
            <<<'SQL'
SELECT TABLE_TYPE AS table_type,
       ENGINE AS engine
FROM information_schema.TABLES
WHERE CAST(TABLE_SCHEMA AS BINARY) = CAST(DATABASE() AS BINARY)
  AND CAST(TABLE_NAME AS BINARY) = CAST(:migration AS BINARY)
SQL,
            [':migration' => $migrationTable]
        )->queryAll();
        if (!is_array($rows) || count($rows) !== 1) {
            throw new RuntimeException('Task 5.1 migration-history table authority is unavailable.');
        }
        $row = $this->lowercaseRow($rows[0]);
        if (($row['table_type'] ?? null) !== 'BASE TABLE'
            || ($row['engine'] ?? null) !== 'InnoDB') {
            throw new RuntimeException(
                'Task 5.1 migration-history table must be an exact InnoDB base table.'
            );
        }
    }

    private function assertExactHistoryRow(
        Connection $db,
        string $migrationTable,
        string $exactMigration
    ): void {
        if (preg_match('/\Am\d{6}_\d{6}_[a-z0-9_]+\z/D', $exactMigration) !== 1) {
            throw new RuntimeException('Task 5.1 exact migration identifier is invalid.');
        }
        $rows = $db->createCommand(
            'SELECT [[version]], [[apply_time]] FROM '
                . $db->quoteTableName($migrationTable)
                . ' WHERE [[version]] = :version FOR SHARE',
            [':version' => $exactMigration]
        )->queryAll();
        if (count($rows) !== 1) {
            throw new RuntimeException('Task 5.1 exact migration-history row is unavailable.');
        }
        $row = $this->lowercaseRow($rows[0]);
        if (!isset($row['version'], $row['apply_time'])
            || !is_string($row['version'])
            || !hash_equals($exactMigration, $row['version'])
            || !$this->isPositiveInteger($row['apply_time'])) {
            throw new RuntimeException('Task 5.1 exact migration-history row is invalid.');
        }
    }

    /**
     * @param array{migration:string,execution:string,transition:string,prefix:string,database:string} $names
     * @return array<string,mixed>
     */
    private function readActualManifest(Connection $db, array $names): array
    {
        $params = [':execution' => $names['execution'], ':transition' => $names['transition']];
        $tablePredicate = $this->tablePredicate('TABLE_SCHEMA', 'TABLE_NAME');

        $tableRows = $this->boundedQueryAll($db, <<<SQL
SELECT TABLE_NAME AS table_name,
       TABLE_TYPE AS table_type,
       ENGINE AS engine,
       TABLE_COLLATION AS table_collation
FROM information_schema.TABLES
WHERE {$tablePredicate}
SQL, $params);
        $tables = $this->normalizeRows($tableRows, function (array $row): array {
            return [
                'name' => $this->requiredString($row, 'table_name'),
                'type' => $this->requiredString($row, 'table_type'),
                'engine' => $this->requiredString($row, 'engine'),
                'collation' => $this->requiredString($row, 'table_collation'),
            ];
        }, static fn(array $row): string => $row['name']);

        $columnRows = $this->boundedQueryAll($db, <<<SQL
SELECT TABLE_NAME AS table_name,
       COLUMN_NAME AS column_name,
       ORDINAL_POSITION AS ordinal_position,
       DATA_TYPE AS data_type,
       COLUMN_TYPE AS column_type,
       IS_NULLABLE AS is_nullable,
       COLUMN_DEFAULT AS column_default,
       CHARACTER_MAXIMUM_LENGTH AS character_maximum_length,
       CHARACTER_OCTET_LENGTH AS character_octet_length,
       DATETIME_PRECISION AS datetime_precision,
       CHARACTER_SET_NAME AS character_set_name,
       COLLATION_NAME AS collation_name,
       EXTRA AS extra,
       GENERATION_EXPRESSION AS generation_expression
FROM information_schema.COLUMNS
WHERE {$tablePredicate}
SQL, $params);
        $columns = $this->normalizeRows($columnRows, function (array $row): array {
            $columnType = strtolower($this->requiredString($row, 'column_type'));
            return [
                'table' => $this->requiredString($row, 'table_name'),
                'name' => $this->requiredString($row, 'column_name'),
                'position' => $this->requiredInteger($row, 'ordinal_position'),
                'dataType' => strtolower($this->requiredString($row, 'data_type')),
                'unsigned' => preg_match('/(?:^|\s)unsigned(?:\s|$)/D', $columnType) === 1,
                'zerofill' => preg_match('/(?:^|\s)zerofill(?:\s|$)/D', $columnType) === 1,
                'characterLength' => $this->nullableInteger($row['character_maximum_length'] ?? null),
                'characterOctetLength' => $this->nullableInteger(
                    $row['character_octet_length'] ?? null
                ),
                'datetimePrecision' => $this->nullableInteger($row['datetime_precision'] ?? null),
                'nullable' => $this->yesNo($row, 'is_nullable'),
                'default' => $this->nullableScalarString($row['column_default'] ?? null),
                'charset' => $this->nullableScalarString($row['character_set_name'] ?? null),
                'collation' => $this->nullableScalarString($row['collation_name'] ?? null),
                'extra' => strtolower($this->requiredString($row, 'extra', true)),
                'generation' => $this->requiredString($row, 'generation_expression', true),
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . sprintf('%04d', $row['position']));

        $indexRows = $this->boundedQueryAll($db, <<<SQL
SELECT TABLE_NAME AS table_name,
       INDEX_NAME AS index_name,
       NON_UNIQUE AS non_unique,
       SEQ_IN_INDEX AS seq_in_index,
       COLUMN_NAME AS column_name,
       COLLATION AS collation,
       SUB_PART AS sub_part,
       INDEX_TYPE AS index_type,
       IS_VISIBLE AS is_visible,
       EXPRESSION AS expression
FROM information_schema.STATISTICS
WHERE {$tablePredicate}
SQL, $params);
        $indexes = $this->normalizeRows($indexRows, function (array $row): array {
            $nonUnique = $this->requiredInteger($row, 'non_unique');
            if ($nonUnique !== 0 && $nonUnique !== 1) {
                throw new RuntimeException('Task 5.1 index uniqueness metadata is invalid.');
            }
            return [
                'table' => $this->requiredString($row, 'table_name'),
                'name' => $this->requiredString($row, 'index_name'),
                'unique' => $nonUnique === 0,
                'position' => $this->requiredInteger($row, 'seq_in_index'),
                'column' => $this->requiredString($row, 'column_name'),
                'collation' => $this->nullableScalarString($row['collation'] ?? null),
                'subPart' => $this->nullableInteger($row['sub_part'] ?? null),
                'type' => $this->requiredString($row, 'index_type'),
                'visible' => $this->requiredString($row, 'is_visible'),
                'expression' => $this->nullableScalarString($row['expression'] ?? null),
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . $row['name'] . "\0" . sprintf('%04d', $row['position']));

        $constraintPredicate = $this->tablePredicate('tc.TABLE_SCHEMA', 'tc.TABLE_NAME');
        $constraintRows = $this->boundedQueryAll($db, <<<SQL
SELECT tc.TABLE_NAME AS table_name,
       tc.CONSTRAINT_NAME AS constraint_name,
       tc.CONSTRAINT_TYPE AS constraint_type,
       tc.ENFORCED AS enforced,
       cc.CHECK_CLAUSE AS check_clause
FROM information_schema.TABLE_CONSTRAINTS AS tc
LEFT JOIN information_schema.CHECK_CONSTRAINTS AS cc
  ON CAST(cc.CONSTRAINT_SCHEMA AS BINARY) = CAST(tc.CONSTRAINT_SCHEMA AS BINARY)
 AND CAST(cc.CONSTRAINT_NAME AS BINARY) = CAST(tc.CONSTRAINT_NAME AS BINARY)
WHERE {$constraintPredicate}
SQL, $params);
        $constraints = $this->normalizeRows($constraintRows, function (array $row): array {
            $type = $this->requiredString($row, 'constraint_type');
            $clause = $row['check_clause'] ?? null;
            if ($type === 'CHECK') {
                if (!is_string($clause)) {
                    throw new RuntimeException('Task 5.1 CHECK clause metadata is unavailable.');
                }
                $clause = self::canonicalizeRestrictedExpression($clause);
            } elseif ($clause !== null) {
                throw new RuntimeException('Task 5.1 non-CHECK constraint has unexpected clause metadata.');
            }
            return [
                'table' => $this->requiredString($row, 'table_name'),
                'name' => $this->requiredString($row, 'constraint_name'),
                'type' => $type,
                'enforced' => $this->requiredString($row, 'enforced'),
                'check' => $clause,
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . $row['type'] . "\0" . $row['name']);

        $fkPredicate = $this->tablePredicate('rc.CONSTRAINT_SCHEMA', 'rc.TABLE_NAME');
        $foreignKeyRows = $this->boundedQueryAll($db, <<<SQL
SELECT rc.TABLE_NAME AS table_name,
       rc.CONSTRAINT_NAME AS constraint_name,
       rc.REFERENCED_TABLE_NAME AS referenced_table_name,
       rc.UNIQUE_CONSTRAINT_SCHEMA AS unique_constraint_schema,
       rc.UNIQUE_CONSTRAINT_NAME AS unique_constraint_name,
       rc.MATCH_OPTION AS match_option,
       rc.UPDATE_RULE AS update_rule,
       rc.DELETE_RULE AS delete_rule,
       kcu.COLUMN_NAME AS column_name,
       kcu.ORDINAL_POSITION AS ordinal_position,
       kcu.REFERENCED_TABLE_SCHEMA AS referenced_table_schema,
       kcu.REFERENCED_COLUMN_NAME AS referenced_column_name,
       kcu.POSITION_IN_UNIQUE_CONSTRAINT AS position_in_unique_constraint
FROM information_schema.REFERENTIAL_CONSTRAINTS AS rc
JOIN information_schema.KEY_COLUMN_USAGE AS kcu
  ON CAST(kcu.CONSTRAINT_SCHEMA AS BINARY) = CAST(rc.CONSTRAINT_SCHEMA AS BINARY)
 AND CAST(kcu.CONSTRAINT_NAME AS BINARY) = CAST(rc.CONSTRAINT_NAME AS BINARY)
 AND CAST(kcu.TABLE_NAME AS BINARY) = CAST(rc.TABLE_NAME AS BINARY)
WHERE {$fkPredicate}
SQL, $params);
        $foreignKeys = $this->normalizeRows($foreignKeyRows, function (array $row): array {
            return [
                'table' => $this->requiredString($row, 'table_name'),
                'name' => $this->requiredString($row, 'constraint_name'),
                'column' => $this->requiredString($row, 'column_name'),
                'position' => $this->requiredInteger($row, 'ordinal_position'),
                'referencedSchema' => $this->requiredString($row, 'referenced_table_schema'),
                'referencedTable' => $this->requiredString($row, 'referenced_table_name'),
                'referencedColumn' => $this->requiredString($row, 'referenced_column_name'),
                'uniqueSchema' => $this->requiredString($row, 'unique_constraint_schema'),
                'uniqueName' => $this->requiredString($row, 'unique_constraint_name'),
                'uniquePosition' => $this->requiredInteger($row, 'position_in_unique_constraint'),
                'match' => $this->requiredString($row, 'match_option'),
                'update' => $this->requiredString($row, 'update_rule'),
                'delete' => $this->requiredString($row, 'delete_rule'),
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . $row['name'] . "\0" . sprintf('%04d', $row['position']));

        $triggerPredicate = $this->tablePredicate('EVENT_OBJECT_SCHEMA', 'EVENT_OBJECT_TABLE');
        $triggerRows = $this->boundedQueryAll($db, <<<SQL
SELECT TRIGGER_NAME AS trigger_name,
       EVENT_OBJECT_TABLE AS event_object_table,
       EVENT_MANIPULATION AS event_manipulation,
       ACTION_TIMING AS action_timing,
       ACTION_ORIENTATION AS action_orientation,
       ACTION_ORDER AS action_order,
       ACTION_CONDITION AS action_condition,
       ACTION_STATEMENT AS action_statement
FROM information_schema.TRIGGERS
WHERE CAST(TRIGGER_SCHEMA AS BINARY) = CAST(DATABASE() AS BINARY)
  AND {$triggerPredicate}
SQL, $params);
        $triggers = $this->normalizeRows($triggerRows, function (array $row): array {
            if (($row['action_condition'] ?? null) !== null) {
                throw new RuntimeException('Task 5.1 trigger condition metadata is unexpected.');
            }
            return [
                'name' => $this->requiredString($row, 'trigger_name'),
                'table' => $this->requiredString($row, 'event_object_table'),
                'event' => $this->requiredString($row, 'event_manipulation'),
                'timing' => $this->requiredString($row, 'action_timing'),
                'orientation' => $this->requiredString($row, 'action_orientation'),
                'order' => $this->requiredInteger($row, 'action_order'),
                'action' => self::canonicalizeTriggerAction(
                    $this->requiredString($row, 'action_statement')
                ),
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . $row['event'] . "\0" . $row['timing'] . "\0" . $row['name']);

        $partitionRows = $this->boundedQueryAll($db, <<<SQL
SELECT TABLE_NAME AS table_name,
       PARTITION_NAME AS partition_name,
       SUBPARTITION_NAME AS subpartition_name,
       PARTITION_METHOD AS partition_method,
       SUBPARTITION_METHOD AS subpartition_method
FROM information_schema.PARTITIONS
WHERE {$tablePredicate}
SQL, $params);
        $partitions = $this->normalizeRows($partitionRows, function (array $row): array {
            return [
                'table' => $this->requiredString($row, 'table_name'),
                'partition' => $this->nullableScalarString($row['partition_name'] ?? null),
                'subpartition' => $this->nullableScalarString($row['subpartition_name'] ?? null),
                'method' => $this->nullableScalarString($row['partition_method'] ?? null),
                'submethod' => $this->nullableScalarString($row['subpartition_method'] ?? null),
            ];
        }, static fn(array $row): string => $row['table'] . "\0" . ($row['partition'] ?? ''));

        return [
            'format' => self::FORMAT,
            'database' => $names['database'],
            'prefix' => $names['prefix'],
            'tables' => $tables,
            'columns' => $columns,
            'indexes' => $indexes,
            'constraints' => $constraints,
            'foreignKeys' => $foreignKeys,
            'triggers' => $triggers,
            'partitions' => $partitions,
        ];
    }

    private function tablePredicate(string $schemaColumn, string $tableColumn): string
    {
        return 'CAST(' . $schemaColumn . ' AS BINARY) = CAST(DATABASE() AS BINARY)'
            . ' AND (CAST(' . $tableColumn . ' AS BINARY) = CAST(:execution AS BINARY)'
            . ' OR CAST(' . $tableColumn . ' AS BINARY) = CAST(:transition AS BINARY))';
    }

    /**
     * @param array<string,mixed> $params
     * @return list<array<string,mixed>>
     */
    private function boundedQueryAll(Connection $db, string $sql, array $params): array
    {
        $rows = $db->createCommand($sql, $params)->queryAll();
        if (!is_array($rows) || count($rows) > self::MAX_METADATA_ROWS) {
            throw new RuntimeException('Task 5.1 metadata result exceeded its fail-closed bound.');
        }
        return array_values($rows);
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @param callable(array<string,mixed>):array<string,mixed> $normalizer
     * @param callable(array<string,mixed>):string $key
     * @return list<array<string,mixed>>
     */
    private function normalizeRows(array $rows, callable $normalizer, callable $key): array
    {
        $normalized = [];
        $seen = [];
        foreach ($rows as $rawRow) {
            $row = $normalizer($this->lowercaseRow($rawRow));
            $rowKey = $key($row);
            if ($rowKey === '' || isset($seen[$rowKey])) {
                throw new RuntimeException('Task 5.1 metadata contains a duplicate canonical row.');
            }
            $seen[$rowKey] = true;
            $normalized[] = $row;
        }
        usort($normalized, static fn(array $left, array $right): int => strcmp($key($left), $key($right)));
        return $normalized;
    }

    /** @return array<string,mixed> */
    private function lowercaseRow(mixed $row): array
    {
        if (!is_array($row)) {
            throw new RuntimeException('Task 5.1 metadata row is invalid.');
        }
        $lower = [];
        foreach ($row as $key => $value) {
            if (!is_string($key)) {
                throw new RuntimeException('Task 5.1 metadata column name is invalid.');
            }
            $lowerKey = strtolower($key);
            if (array_key_exists($lowerKey, $lower)) {
                throw new RuntimeException('Task 5.1 metadata column name is ambiguous.');
            }
            $lower[$lowerKey] = $value;
        }
        return $lower;
    }

    private function requiredString(array $row, string $key, bool $allowEmpty = false): string
    {
        if (!array_key_exists($key, $row) || !is_string($row[$key])
            || (!$allowEmpty && $row[$key] === '') || str_contains($row[$key], "\0")) {
            throw new RuntimeException('Task 5.1 metadata string is invalid.');
        }
        return $row[$key];
    }

    private function requiredInteger(array $row, string $key): int
    {
        if (!array_key_exists($key, $row)) {
            throw new RuntimeException('Task 5.1 metadata integer is unavailable.');
        }
        $value = $row[$key];
        if (is_int($value) && $value >= 0) {
            return $value;
        }
        if (!is_string($value) || preg_match('/\A\d+\z/D', $value) !== 1) {
            throw new RuntimeException('Task 5.1 metadata integer is invalid.');
        }
        return (int)$value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        return $this->requiredInteger(['value' => $value], 'value');
    }

    private function nullableScalarString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new RuntimeException('Task 5.1 metadata scalar is invalid.');
        }
        $string = (string)$value;
        if (str_contains($string, "\0")) {
            throw new RuntimeException('Task 5.1 metadata scalar contains NUL.');
        }
        return $string;
    }

    private function yesNo(array $row, string $key): bool
    {
        $value = $this->requiredString($row, $key);
        if ($value === 'YES') {
            return true;
        }
        if ($value === 'NO') {
            return false;
        }
        throw new RuntimeException('Task 5.1 YES/NO metadata is invalid.');
    }

    private function isPositiveInteger(mixed $value): bool
    {
        if (is_int($value)) {
            return $value > 0;
        }
        return is_string($value)
            && preg_match('/\A[1-9]\d*\z/D', $value) === 1;
    }

    /**
     * @param array{migration:string,execution:string,transition:string,prefix:string,database:string} $names
     * @return array<string,mixed>
     */
    private function expectedManifest(array $names): array
    {
        $execution = $names['execution'];
        $transition = $names['transition'];

        $columns = [];
        $addCharacter = static function (
            string $table,
            string $name,
            int $position,
            string $type,
            int $length,
            bool $nullable
        ) use (&$columns): void {
            $columns[] = self::expectedColumn(
                $table,
                $name,
                $position,
                $type,
                false,
                $length,
                null,
                $nullable,
                null,
                'ascii',
                'ascii_bin'
            );
        };
        $addInteger = static function (
            string $table,
            string $name,
            int $position,
            string $type,
            bool $nullable,
            ?string $default = null,
            string $extra = ''
        ) use (&$columns): void {
            $columns[] = self::expectedColumn(
                $table,
                $name,
                $position,
                $type,
                true,
                null,
                null,
                $nullable,
                $default,
                null,
                null,
                $extra
            );
        };
        $addDateTime = static function (
            string $table,
            string $name,
            int $position,
            bool $nullable
        ) use (&$columns): void {
            $columns[] = self::expectedColumn(
                $table,
                $name,
                $position,
                'datetime',
                false,
                null,
                3,
                $nullable
            );
        };
        $addBlob = static function (
            string $table,
            string $name,
            int $position
        ) use (&$columns): void {
            $columns[] = self::expectedColumn(
                $table,
                $name,
                $position,
                'blob',
                false,
                65535,
                null,
                true
            );
        };

        $addCharacter($execution, 'execution_id', 1, 'varchar', 128, false);
        $addCharacter($execution, 'approval_ref', 2, 'varchar', 191, false);
        $addCharacter($execution, 'stage_b_sha256', 3, 'char', 64, false);
        $addCharacter($execution, 'claim_capability_sha256', 4, 'char', 64, false);
        $addCharacter($execution, 'coordinator_origin', 5, 'varchar', 255, false);
        $addCharacter($execution, 'coordinator_server_publish_sha', 6, 'char', 40, false);
        $addCharacter($execution, 'state', 7, 'varchar', 16, false);
        $addInteger($execution, 'state_version', 8, 'int', false, '0');
        $addInteger($execution, 'claim_count', 9, 'smallint', false, '0');
        $addInteger($execution, 'consumption_count', 10, 'smallint', false, '0');
        $addDateTime($execution, 'issued_at', 11, false);
        $addDateTime($execution, 'expires_at', 12, false);
        $addDateTime($execution, 'claimed_at', 13, true);
        $addDateTime($execution, 'consumed_at', 14, true);
        $addBlob($execution, 'claim_receipt_canonical', 15);
        $addCharacter($execution, 'claim_receipt_sha256', 16, 'char', 64, true);
        $addCharacter($execution, 'runner_export_receipt_ref', 17, 'varchar', 512, true);
        $addCharacter($execution, 'runner_export_receipt_sha256', 18, 'char', 64, true);
        $addBlob($execution, 'consumption_receipt_canonical', 19);
        $addCharacter($execution, 'consumption_receipt_sha256', 20, 'char', 64, true);
        $addDateTime($execution, 'created_at', 21, false);
        $addDateTime($execution, 'updated_at', 22, false);
        $addCharacter(
            $execution,
            'production_direct_matrix_evidence_ref',
            23,
            'varchar',
            512,
            false
        );
        $addCharacter(
            $execution,
            'production_direct_matrix_subject_digest',
            24,
            'char',
            64,
            false
        );

        $addInteger($transition, 'id', 1, 'bigint', false, null, 'auto_increment');
        $addCharacter($transition, 'execution_id', 2, 'varchar', 128, false);
        $addInteger($transition, 'ordinal', 3, 'smallint', false);
        $addCharacter($transition, 'from_state', 4, 'varchar', 16, true);
        $addCharacter($transition, 'to_state', 5, 'varchar', 16, false);
        $addInteger($transition, 'state_version', 6, 'int', false);
        $addCharacter($transition, 'evidence_sha256', 7, 'char', 64, false);
        $addDateTime($transition, 'occurred_at', 8, false);

        $indexes = [
            self::expectedIndex($execution, 'PRIMARY', true, 1, 'execution_id'),
            self::expectedIndex($execution, 'idx_task51_stage_b_runner_export_sha256', false, 1, 'runner_export_receipt_sha256'),
            self::expectedIndex($execution, 'idx_task51_stage_b_state_expiry', false, 1, 'state'),
            self::expectedIndex($execution, 'idx_task51_stage_b_state_expiry', false, 2, 'expires_at'),
            self::expectedIndex($execution, 'uq_task51_stage_b_approval_ref', true, 1, 'approval_ref'),
            self::expectedIndex($execution, 'uq_task51_stage_b_claim_capability_sha256', true, 1, 'claim_capability_sha256'),
            self::expectedIndex($execution, 'uq_task51_stage_b_sha256', true, 1, 'stage_b_sha256'),
            self::expectedIndex($transition, 'PRIMARY', true, 1, 'id'),
            self::expectedIndex($transition, 'idx_task51_stage_b_transition_time', false, 1, 'occurred_at'),
            self::expectedIndex($transition, 'idx_task51_stage_b_transition_time', false, 2, 'execution_id'),
            self::expectedIndex($transition, 'uq_task51_stage_b_transition_ordinal', true, 1, 'execution_id'),
            self::expectedIndex($transition, 'uq_task51_stage_b_transition_ordinal', true, 2, 'ordinal'),
        ];

        $checks = [
            'ck_task51_stage_b_counts' => "(state = 'ISSUED' AND state_version = 0 AND claim_count = 0 AND consumption_count = 0) OR (state = 'CLAIMED' AND state_version = 1 AND claim_count = 1 AND consumption_count = 0) OR (state = 'CONSUMED' AND state_version = 2 AND claim_count = 1 AND consumption_count = 1)",
            'ck_task51_stage_b_receipts' => "(state = 'ISSUED' AND claimed_at IS NULL AND consumed_at IS NULL AND claim_receipt_canonical IS NULL AND claim_receipt_sha256 IS NULL AND runner_export_receipt_ref IS NULL AND runner_export_receipt_sha256 IS NULL AND consumption_receipt_canonical IS NULL AND consumption_receipt_sha256 IS NULL) OR (state = 'CLAIMED' AND claimed_at IS NOT NULL AND consumed_at IS NULL AND claim_receipt_canonical IS NOT NULL AND claim_receipt_sha256 IS NOT NULL AND runner_export_receipt_ref IS NULL AND runner_export_receipt_sha256 IS NULL AND consumption_receipt_canonical IS NULL AND consumption_receipt_sha256 IS NULL) OR (state = 'CONSUMED' AND claimed_at IS NOT NULL AND consumed_at IS NOT NULL AND claim_receipt_canonical IS NOT NULL AND claim_receipt_sha256 IS NOT NULL AND runner_export_receipt_ref IS NOT NULL AND runner_export_receipt_sha256 IS NOT NULL AND consumption_receipt_canonical IS NOT NULL AND consumption_receipt_sha256 IS NOT NULL)",
            'ck_task51_stage_b_state' => "state IN ('ISSUED', 'CLAIMED', 'CONSUMED')",
            'ck_task51_stage_b_time_order' => 'issued_at < expires_at AND created_at >= issued_at AND created_at < expires_at AND updated_at >= created_at AND updated_at < expires_at AND (claimed_at IS NULL OR (claimed_at >= issued_at AND claimed_at < expires_at)) AND (consumed_at IS NULL OR (claimed_at IS NOT NULL AND consumed_at >= claimed_at AND consumed_at < expires_at))',
            'ck_task51_stage_b_transition_evidence_sha256' => "evidence_sha256 REGEXP '^[0-9a-f]{64}$'",
            'ck_task51_stage_b_transition_shape' => "(ordinal = 0 AND from_state IS NULL AND to_state = 'ISSUED' AND state_version = 0) OR (ordinal = 1 AND from_state IS NOT NULL AND from_state = 'ISSUED' AND to_state = 'CLAIMED' AND state_version = 1) OR (ordinal = 2 AND from_state IS NOT NULL AND from_state = 'CLAIMED' AND to_state = 'CONSUMED' AND state_version = 2)",
        ];

        $constraints = [
            self::expectedConstraint($execution, 'PRIMARY', 'PRIMARY KEY'),
            self::expectedConstraint($execution, 'uq_task51_stage_b_approval_ref', 'UNIQUE'),
            self::expectedConstraint($execution, 'uq_task51_stage_b_claim_capability_sha256', 'UNIQUE'),
            self::expectedConstraint($execution, 'uq_task51_stage_b_sha256', 'UNIQUE'),
            self::expectedConstraint($transition, 'PRIMARY', 'PRIMARY KEY'),
            self::expectedConstraint($transition, 'fk_task51_stage_b_transition_execution', 'FOREIGN KEY'),
            self::expectedConstraint($transition, 'uq_task51_stage_b_transition_ordinal', 'UNIQUE'),
        ];
        foreach ($checks as $name => $expression) {
            $table = str_contains($name, '_transition_') ? $transition : $execution;
            $constraints[] = self::expectedConstraint(
                $table,
                $name,
                'CHECK',
                self::canonicalizeRestrictedExpression($expression)
            );
        }

        $signal = self::canonicalizeTriggerAction(
            "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task 5.1 transition ledger is append-only'"
        );
        $triggers = [
            [
                'name' => 'trg_task51_stage_b_transition_no_delete',
                'table' => $transition,
                'event' => 'DELETE',
                'timing' => 'BEFORE',
                'orientation' => 'ROW',
                'order' => 1,
                'action' => $signal,
            ],
            [
                'name' => 'trg_task51_stage_b_transition_no_update',
                'table' => $transition,
                'event' => 'UPDATE',
                'timing' => 'BEFORE',
                'orientation' => 'ROW',
                'order' => 1,
                'action' => $signal,
            ],
        ];

        $foreignKeys = [[
            'table' => $transition,
            'name' => 'fk_task51_stage_b_transition_execution',
            'column' => 'execution_id',
            'position' => 1,
            'referencedSchema' => $names['database'],
            'referencedTable' => $execution,
            'referencedColumn' => 'execution_id',
            'uniqueSchema' => $names['database'],
            'uniqueName' => 'PRIMARY',
            'uniquePosition' => 1,
            'match' => 'NONE',
            'update' => 'RESTRICT',
            'delete' => 'RESTRICT',
        ]];

        $tables = [
            ['name' => $execution, 'type' => 'BASE TABLE', 'engine' => 'InnoDB', 'collation' => 'ascii_bin'],
            ['name' => $transition, 'type' => 'BASE TABLE', 'engine' => 'InnoDB', 'collation' => 'ascii_bin'],
        ];
        $partitions = [
            ['table' => $execution, 'partition' => null, 'subpartition' => null, 'method' => null, 'submethod' => null],
            ['table' => $transition, 'partition' => null, 'subpartition' => null, 'method' => null, 'submethod' => null],
        ];

        usort($columns, static fn(array $left, array $right): int => strcmp(
            $left['table'] . "\0" . sprintf('%04d', $left['position']),
            $right['table'] . "\0" . sprintf('%04d', $right['position'])
        ));
        usort($indexes, static fn(array $left, array $right): int => strcmp(
            $left['table'] . "\0" . $left['name'] . "\0" . sprintf('%04d', $left['position']),
            $right['table'] . "\0" . $right['name'] . "\0" . sprintf('%04d', $right['position'])
        ));
        usort($constraints, static fn(array $left, array $right): int => strcmp(
            $left['table'] . "\0" . $left['type'] . "\0" . $left['name'],
            $right['table'] . "\0" . $right['type'] . "\0" . $right['name']
        ));
        usort($triggers, static fn(array $left, array $right): int => strcmp(
            $left['table'] . "\0" . $left['event'] . "\0" . $left['timing'] . "\0" . $left['name'],
            $right['table'] . "\0" . $right['event'] . "\0" . $right['timing'] . "\0" . $right['name']
        ));

        return [
            'format' => self::FORMAT,
            'database' => $names['database'],
            'prefix' => $names['prefix'],
            'tables' => $tables,
            'columns' => $columns,
            'indexes' => $indexes,
            'constraints' => $constraints,
            'foreignKeys' => $foreignKeys,
            'triggers' => $triggers,
            'partitions' => $partitions,
        ];
    }

    /** @return array<string,mixed> */
    private static function expectedColumn(
        string $table,
        string $name,
        int $position,
        string $dataType,
        bool $unsigned,
        ?int $characterLength,
        ?int $datetimePrecision,
        bool $nullable,
        ?string $default = null,
        ?string $charset = null,
        ?string $collation = null,
        string $extra = ''
    ): array {
        return [
            'table' => $table,
            'name' => $name,
            'position' => $position,
            'dataType' => $dataType,
            'unsigned' => $unsigned,
            'zerofill' => false,
            'characterLength' => $characterLength,
            'characterOctetLength' => $characterLength,
            'datetimePrecision' => $datetimePrecision,
            'nullable' => $nullable,
            'default' => $default,
            'charset' => $charset,
            'collation' => $collation,
            'extra' => $extra,
            'generation' => '',
        ];
    }

    /** @return array<string,mixed> */
    private static function expectedIndex(
        string $table,
        string $name,
        bool $unique,
        int $position,
        string $column
    ): array {
        return [
            'table' => $table,
            'name' => $name,
            'unique' => $unique,
            'position' => $position,
            'column' => $column,
            'collation' => 'A',
            'subPart' => null,
            'type' => 'BTREE',
            'visible' => 'YES',
            'expression' => null,
        ];
    }

    /** @return array<string,mixed> */
    private static function expectedConstraint(
        string $table,
        string $name,
        string $type,
        ?string $check = null
    ): array {
        return [
            'table' => $table,
            'name' => $name,
            'type' => $type,
            'enforced' => 'YES',
            'check' => $check,
        ];
    }

    /** @param array<string,mixed> $value */
    private function canonicalJson(array $value): string
    {
        return self::canonicalJsonStatic($value);
    }

    /** @param array<mixed> $value */
    private static function canonicalJsonStatic(array $value): string
    {
        try {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        } catch (JsonException $exception) {
            throw new RuntimeException('Task 5.1 canonical JSON encoding failed.', 0, $exception);
        }
    }
}

/** Restricted parser for the six fixed CHECKs and two fixed SIGNAL actions. */
final class Task51RestrictedSqlParser
{
    /** @var list<array{type:string,value:string}> */
    private array $tokens;
    private int $offset = 0;

    public function __construct(string $sql)
    {
        $this->tokens = $this->tokenize($sql);
    }

    /** @return array<mixed> */
    public function parseBooleanExpression(): array
    {
        $expression = $this->parseOr();
        $this->expectEnd();
        return $expression;
    }

    /** @return array<mixed> */
    public function parseSignalAction(): array
    {
        $this->expectKeyword('SIGNAL');
        $this->expectKeyword('SQLSTATE');
        $this->acceptKeyword('VALUE');
        $sqlState = $this->expectType('string')['value'];
        $this->expectKeyword('SET');
        $messageName = $this->expectType('identifier')['value'];
        if ($messageName !== 'message_text') {
            throw new RuntimeException('Task 5.1 trigger SET target is invalid.');
        }
        $this->expectValue('operator', '=');
        $message = $this->expectType('string')['value'];
        $this->expectEnd();
        return ['signal', $sqlState, 'message_text', $message];
    }

    /** @return array<mixed> */
    private function parseOr(): array
    {
        $items = [$this->parseAnd()];
        while ($this->acceptKeyword('OR')) {
            $items[] = $this->parseAnd();
        }
        return $this->combineAssociative('or', $items);
    }

    /** @return array<mixed> */
    private function parseAnd(): array
    {
        $items = [$this->parsePrimary()];
        while ($this->acceptKeyword('AND')) {
            $items[] = $this->parsePrimary();
        }
        return $this->combineAssociative('and', $items);
    }

    /**
     * MySQL may add redundant parenthesized nodes while printing a stored
     * expression. Flatten only the same associative operator; operand order
     * and every security-relevant token remain exact.
     *
     * @param list<array<mixed>> $items
     * @return array<mixed>
     */
    private function combineAssociative(string $operator, array $items): array
    {
        $flattened = [];
        foreach ($items as $item) {
            if (($item[0] ?? null) === $operator) {
                array_push($flattened, ...array_slice($item, 1));
            } else {
                $flattened[] = $item;
            }
        }
        return count($flattened) === 1 ? $flattened[0] : [$operator, ...$flattened];
    }

    /** @return array<mixed> */
    private function parsePrimary(): array
    {
        if ($this->acceptValue('symbol', '(')) {
            // MySQL rewrites the REGEXP infix operator as REGEXP_LIKE().
            if ($this->peekValue('identifier', 'regexp_like')) {
                $expression = $this->parseRegexpLikeCall();
            } else {
                $expression = $this->parseOr();
            }
            $this->expectValue('symbol', ')');
            return $expression;
        }
        if ($this->peekValue('identifier', 'regexp_like')) {
            return $this->parseRegexpLikeCall();
        }
        return $this->parsePredicate();
    }

    /** @return array<mixed> */
    private function parsePredicate(): array
    {
        $left = $this->parseOperand();
        if (($token = $this->acceptType('operator')) !== null) {
            if (!in_array($token['value'], ['=', '<', '>='], true)) {
                throw new RuntimeException('Task 5.1 CHECK comparison operator is forbidden.');
            }
            return ['compare', $token['value'], $left, $this->parseOperand()];
        }
        if ($this->acceptKeyword('IN')) {
            $this->expectValue('symbol', '(');
            $values = [$this->parseOperand()];
            while ($this->acceptValue('symbol', ',')) {
                $values[] = $this->parseOperand();
            }
            $this->expectValue('symbol', ')');
            return ['in', $left, $values];
        }
        if ($this->acceptKeyword('IS')) {
            $negated = $this->acceptKeyword('NOT');
            $this->expectKeyword('NULL');
            return ['is-null', $negated, $left];
        }
        if ($this->acceptKeyword('REGEXP')) {
            return ['regexp', $left, $this->parseOperand()];
        }
        throw new RuntimeException('Task 5.1 CHECK predicate is outside the restricted grammar.');
    }

    /** @return array<mixed> */
    private function parseRegexpLikeCall(): array
    {
        $this->expectValue('identifier', 'regexp_like');
        $this->expectValue('symbol', '(');
        $left = $this->parseOperand();
        $this->expectValue('symbol', ',');
        $pattern = $this->parseOperand();
        $this->expectValue('symbol', ')');
        return ['regexp', $left, $pattern];
    }

    /** @return array<mixed> */
    private function parseOperand(): array
    {
        $token = $this->tokens[$this->offset] ?? null;
        if ($token === null || !in_array($token['type'], ['identifier', 'number', 'string'], true)) {
            throw new RuntimeException('Task 5.1 CHECK operand is invalid.');
        }
        $this->offset++;
        return [$token['type'], $token['value']];
    }

    /** @return list<array{type:string,value:string}> */
    private function tokenize(string $sql): array
    {
        $tokens = [];
        $length = strlen($sql);
        for ($index = 0; $index < $length;) {
            $character = $sql[$index];
            if (ctype_space($character)) {
                $index++;
                continue;
            }
            if ($character === '`') {
                $end = strpos($sql, '`', $index + 1);
                if ($end === false || $end === $index + 1) {
                    throw new RuntimeException('Task 5.1 quoted identifier is invalid.');
                }
                $identifier = substr($sql, $index + 1, $end - $index - 1);
                if (preg_match('/\A[a-z_][a-z0-9_]*\z/Di', $identifier) !== 1) {
                    throw new RuntimeException('Task 5.1 quoted identifier is forbidden.');
                }
                $tokens[] = ['type' => 'identifier', 'value' => strtolower($identifier)];
                $index = $end + 1;
                continue;
            }
            if ($character === "'") {
                [$value, $index] = $this->readString($sql, $index);
                $tokens[] = ['type' => 'string', 'value' => $value];
                continue;
            }
            if (ctype_digit($character)) {
                $start = $index;
                while ($index < $length && ctype_digit($sql[$index])) {
                    $index++;
                }
                $tokens[] = ['type' => 'number', 'value' => substr($sql, $start, $index - $start)];
                continue;
            }
            if (ctype_alpha($character) || $character === '_') {
                $start = $index;
                while ($index < $length && (ctype_alnum($sql[$index]) || $sql[$index] === '_')) {
                    $index++;
                }
                $word = strtolower(substr($sql, $start, $index - $start));
                if (in_array($word, ['_ascii', '_utf8mb3', '_utf8mb4'], true)
                    && $index < $length
                    && ($sql[$index] === "'" || substr($sql, $index, 2) === "\\'")) {
                    if ($sql[$index] === "'") {
                        [$value, $index] = $this->readString($sql, $index);
                    } else {
                        // MySQL 8.0.46 exposes charset-introduced CHECK literals as
                        // _ascii\'value\' in CHECK_CONSTRAINTS.CHECK_CLAUSE. Accept
                        // only those delimiter backslashes after a pinned introducer;
                        // backslashes inside the literal remain forbidden below.
                        [$value, $index] = $this->readMetadataIntroducedString($sql, $index);
                    }
                    if (preg_match('/\A[\x20-\x7e]*\z/D', $value) !== 1) {
                        throw new RuntimeException('Task 5.1 introduced string must remain ASCII.');
                    }
                    $tokens[] = ['type' => 'string', 'value' => $value];
                    continue;
                }
                $keyword = strtoupper($word);
                if (in_array($keyword, [
                    'AND', 'OR', 'IN', 'IS', 'NOT', 'NULL', 'REGEXP',
                    'SIGNAL', 'SQLSTATE', 'VALUE', 'SET',
                ], true)) {
                    $tokens[] = ['type' => 'keyword', 'value' => $keyword];
                } else {
                    $tokens[] = ['type' => 'identifier', 'value' => $word];
                }
                continue;
            }
            if ($character === '(' || $character === ')' || $character === ',') {
                $tokens[] = ['type' => 'symbol', 'value' => $character];
                $index++;
                continue;
            }
            $two = substr($sql, $index, 2);
            if (in_array($two, ['>=', '<=', '<>', '!='], true)) {
                $tokens[] = ['type' => 'operator', 'value' => $two];
                $index += 2;
                continue;
            }
            if ($character === '=' || $character === '<' || $character === '>') {
                $tokens[] = ['type' => 'operator', 'value' => $character];
                $index++;
                continue;
            }
            throw new RuntimeException(
                'Task 5.1 SQL contains a forbidden token at byte '
                    . $index . ' (0x' . bin2hex($character) . '; context='
                    . bin2hex(substr($sql, max(0, $index - 20), 41)) . ').'
            );
        }
        if ($tokens === []) {
            throw new RuntimeException('Task 5.1 SQL token stream is empty.');
        }
        return $tokens;
    }

    /** @return array{0:string,1:int} */
    private function readString(string $sql, int $start): array
    {
        $value = '';
        $length = strlen($sql);
        for ($index = $start + 1; $index < $length; $index++) {
            if ($sql[$index] === "\\") {
                throw new RuntimeException('Task 5.1 SQL backslash escape is forbidden.');
            }
            if ($sql[$index] !== "'") {
                $value .= $sql[$index];
                continue;
            }
            if ($index + 1 < $length && $sql[$index + 1] === "'") {
                $value .= "'";
                $index++;
                continue;
            }
            if (str_contains($value, "\0")) {
                throw new RuntimeException('Task 5.1 SQL string contains NUL.');
            }
            return [$value, $index + 1];
        }
        throw new RuntimeException('Task 5.1 SQL string is unterminated.');
    }

    /**
     * Read MySQL's information_schema-only rendering of an introduced literal:
     * _ascii\'value\'. The caller has already consumed the exact introducer.
     *
     * @return array{0:string,1:int}
     */
    private function readMetadataIntroducedString(string $sql, int $start): array
    {
        if (substr($sql, $start, 2) !== "\\'") {
            throw new RuntimeException('Task 5.1 metadata string delimiter is invalid.');
        }
        $value = '';
        $length = strlen($sql);
        for ($index = $start + 2; $index < $length; $index++) {
            if ($sql[$index] === "'") {
                throw new RuntimeException('Task 5.1 metadata string quote is forbidden.');
            }
            if ($sql[$index] !== "\\") {
                $value .= $sql[$index];
                continue;
            }
            if ($index + 1 < $length && $sql[$index + 1] === "'") {
                if (str_contains($value, "\0")) {
                    throw new RuntimeException('Task 5.1 SQL string contains NUL.');
                }
                return [$value, $index + 2];
            }
            throw new RuntimeException('Task 5.1 SQL backslash escape is forbidden.');
        }
        throw new RuntimeException('Task 5.1 metadata string is unterminated.');
    }

    private function acceptKeyword(string $keyword): bool
    {
        if (!$this->peekValue('keyword', $keyword)) {
            return false;
        }
        $this->offset++;
        return true;
    }

    private function expectKeyword(string $keyword): void
    {
        if (!$this->acceptKeyword($keyword)) {
            throw new RuntimeException('Task 5.1 expected SQL keyword is unavailable.');
        }
    }

    /** @return array{type:string,value:string}|null */
    private function acceptType(string $type): ?array
    {
        $token = $this->tokens[$this->offset] ?? null;
        if ($token === null || $token['type'] !== $type) {
            return null;
        }
        $this->offset++;
        return $token;
    }

    /** @return array{type:string,value:string} */
    private function expectType(string $type): array
    {
        $token = $this->acceptType($type);
        if ($token === null) {
            throw new RuntimeException('Task 5.1 expected SQL token type is unavailable.');
        }
        return $token;
    }

    private function acceptValue(string $type, string $value): bool
    {
        if (!$this->peekValue($type, $value)) {
            return false;
        }
        $this->offset++;
        return true;
    }

    private function expectValue(string $type, string $value): void
    {
        if (!$this->acceptValue($type, $value)) {
            throw new RuntimeException('Task 5.1 expected SQL token is unavailable.');
        }
    }

    private function peekValue(string $type, string $value): bool
    {
        $token = $this->tokens[$this->offset] ?? null;
        return $token !== null && $token['type'] === $type && $token['value'] === $value;
    }

    private function expectEnd(): void
    {
        if ($this->offset !== count($this->tokens)) {
            throw new RuntimeException('Task 5.1 SQL has trailing tokens.');
        }
    }
}
