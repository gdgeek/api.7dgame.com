<?php
// Ephemeral deployment helper. Never replaces files in the live application.
declare(strict_types=1);

umask(0077);
ini_set('display_errors', '0');
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

function failP2(string $reason): never
{
    // Deliberately omit exception text/SQL/config: they can contain credentials.
    throw new RuntimeException($reason);
}

function captureP2(\yii\db\Connection $db, string $expected, string $root, string $configuredCharset): array
{
    $db->createCommand('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ')->execute();
    $db->createCommand('SET TRANSACTION READ ONLY')->execute();
    $tx = $db->beginTransaction();
    try {
        $server = $db->createCommand('SELECT DATABASE() AS database_name, @@hostname AS host, @@port AS port, @@server_id AS server_id, @@version AS version, @@max_allowed_packet AS max_packet, @@character_set_client AS client_charset, @@character_set_connection AS connection_charset, @@character_set_results AS results_charset')->queryOne();
        if ($server['database_name'] !== $expected) failP2('database_identity_changed');
        $routes = ['@restful/v1/verse/publications', '@restful/v1/verse/publication-version'];
        $parents = ['@restful/v1/verse/view', '@restful/v1/verse/update'];
        $names = ['verse', 'verse_code', 'code', 'meta', 'meta_code', 'verse_meta', 'meta_resource', 'resource', 'file', 'manager', 'verse_space', 'space', 'snapshot', 'webmcp_operation', 'scene_publication_revision'];
        $engines = [];
        $allEngines = array_column($db->createCommand('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->queryAll(), 'ENGINE', 'TABLE_NAME');
        foreach ($names as $name) $engines[$name] = $allEngines[$db->tablePrefix . $name] ?? null;
        $schemas = [];
        foreach (['scene_publication_revision', 'webmcp_operation', 'auth_item', 'auth_item_child', 'migration'] as $name) {
            if ($db->getTableSchema('{{%' . $name . '}}', true) !== null) {
                $row = $db->createCommand('SHOW CREATE TABLE ' . $db->quoteTableName($db->tablePrefix . $name))->queryOne();
                $schemas[$name] = array_values($row)[1];
            } else $schemas[$name] = null;
        }
        $query = static fn (string $table) => (new \yii\db\Query())->cache(false)->from('{{%' . $table . '}}');
        $migrationRows = $schemas['migration'] !== null ? $query('migration')->where(['version' => [
            'm260911_230000_add_webmcp_write_receipts', 'm260913_120000_add_scene_publication_history',
        ]])->orderBy('version')->all($db) : [];
        $authItems = $schemas['auth_item'] !== null ? $query('auth_item')->where(['name' => array_merge($parents, $routes)])->orderBy('name')->all($db) : [];
        $authChildren = $schemas['auth_item_child'] !== null ? $query('auth_item_child')->where(['child' => $routes])->orderBy(['parent' => SORT_ASC, 'child' => SORT_ASC])->all($db) : [];
        $snapshotSchema = $db->getTableSchema('{{%snapshot}}', true);
        $parts = [];
        if ($snapshotSchema !== null) {
            foreach (['code', 'data', 'metas', 'resources', 'managers', 'space'] as $name) {
                if (isset($snapshotSchema->columns[$name])) $parts[] = 'OCTET_LENGTH(COALESCE(' . $db->quoteColumnName($name) . ", ''))";
            }
        }
        $snapshotStats = null;
        if ($parts !== []) {
            $length = '(' . implode(' + ', $parts) . ')';
            $snapshotStats = $db->createCommand('SELECT COUNT(*) AS count, COALESCE(SUM(' . $length . '),0) AS total_bytes, COALESCE(MAX(' . $length . '),0) AS max_bytes, COALESCE(ROUND(AVG(' . $length . ')),0) AS avg_bytes, COALESCE(SUM(' . $length . ' > 8388608),0) AS above_8mib FROM ' . $db->quoteTableName($db->tablePrefix . 'snapshot'))->queryOne();
        }
        $archiveStats = $schemas['scene_publication_revision'] !== null
            ? $query('scene_publication_revision')->select(['rows' => 'COUNT(*)', 'bytes' => 'COALESCE(SUM(byte_length),0)'])->one($db) : null;
        $result = [
            'database' => $expected, 'capturedAt' => gmdate('c'),
            'serverFingerprint' => hash('sha256', implode('|', [$server['host'], $server['port'], $server['server_id']])),
            'serverVersion' => $server['version'], 'liveCommit' => is_file($root . '/GIT_COMMIT') ? trim(file_get_contents($root . '/GIT_COMMIT')) : 'unavailable',
            'configuredBusinessCharset' => $configuredCharset,
            'maintenanceCharsets' => ['client' => $server['client_charset'], 'connection' => $server['connection_charset'], 'results' => $server['results_charset']],
            'maxAllowedPacket' => (int) $server['max_packet'],
            // Emulated inserts may escape an 8 MiB body to twice its size.
            'recommendedMinPacketBytes' => 17 * 1024 * 1024,
            'engines' => $engines, 'snapshotBytes' => $snapshotStats,
            'snapshotStatisticsNote' => 'Current mutable snapshots only; excludes outer archive metadata/escaping and cannot measure historical daily publication volume.',
            'archiveStats' => $archiveStats, 'migrationRows' => $migrationRows,
            'authItems' => $authItems, 'authChildren' => $authChildren, 'schemas' => $schemas,
            'scope' => 'P2 affected RBAC rows, P1/P2 migration rows and schema only; no scene bodies, resource content, credentials, or archive bodies.',
        ];
        $tx->rollBack();
        return $result;
    } catch (Throwable $error) {
        if ($tx->isActive) $tx->rollBack();
        throw $error;
    }
}

function readyP2(array $state): array
{
    $issues = [];
    foreach ($state['engines'] as $name => $engine) {
        if ($name === 'scene_publication_revision' && $engine === null) continue;
        if (strtolower((string) $engine) !== 'innodb') $issues[] = 'non_innodb_or_missing:' . $name;
    }
    foreach (['auth_item', 'auth_item_child', 'migration'] as $table) {
        if ($state['schemas'][$table] === null) $issues[] = 'missing:' . $table;
    }
    if (!in_array('m260911_230000_add_webmcp_write_receipts', array_column($state['migrationRows'], 'version'), true)) $issues[] = 'p1_migration_not_recorded';
    foreach (['@restful/v1/verse/view', '@restful/v1/verse/update'] as $name) {
        if (!in_array($name, array_column($state['authItems'], 'name'), true)) $issues[] = 'missing_parent:' . $name;
    }
    if ($state['maxAllowedPacket'] < $state['recommendedMinPacketBytes']) $issues[] = 'packet_below_escaped_8mib_capacity';
    foreach ($state['maintenanceCharsets'] as $charset) if ($charset !== 'utf8mb4') $issues[] = 'maintenance_charset_not_utf8mb4';
    return array_values(array_unique($issues));
}

function runP2Action(\yii\console\Application $app, string $action): void
{
    $app->controller = null;
    ob_start();
    try {
        $exit = $app->runAction('web-mcp-p2-migrate/' . $action, $action === 'up' ? [0 => 1, 'interactive' => false] : []);
    } finally {
        // Suppress echoed SQL exception payloads; Yii may still fwrite non-sensitive progress.
        ob_end_clean();
    }
    if ($exit !== 0) failP2('exact_migration_action_failed:' . $action);
    echo 'P2_ACTION_OK ', $action, PHP_EOL;
}

try {
    $expected = $argv[1] ?? '';
    $phase = $argv[2] ?? '';
    if (!in_array($expected, ['bujiaban', 'bujiaban_development'], true)
        || !in_array($phase, ['preflight', 'backup', 'export', 'plan', 'up', 'verify'], true)
        || count($argv) !== ($phase === 'up' ? 4 : 3)) failP2('invalid_arguments');
    $manifest = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true, 32, JSON_THROW_ON_ERROR);
    foreach ($manifest['files'] as $name => $hash) {
        if (!hash_equals($hash, hash_file('sha256', __DIR__ . '/' . $name))) failP2('bundle_integrity_failure');
    }
    $backupFile = __DIR__ . '/before-' . $expected . '.json';
    if ($phase === 'export') {
        if (!is_file($backupFile)) failP2('backup_missing');
        echo 'BACKUP_BASE64_BEGIN', PHP_EOL, base64_encode(gzencode(file_get_contents($backupFile), 9)), PHP_EOL, 'BACKUP_BASE64_END', PHP_EOL;
        exit(0);
    }
    $root = '/var/www/html/advanced';
    define('YII_DEBUG', false);
    define('YII_ENV', 'prod');
    require $root . '/vendor/autoload.php';
    require $root . '/vendor/yiisoft/yii2/Yii.php';
    require $root . '/common/config/bootstrap.php';
    require $root . '/console/config/bootstrap.php';
    $config = yii\helpers\ArrayHelper::merge(
        require $root . '/common/config/main.php', require $root . '/common/config/main-local.php',
        require $root . '/console/config/main.php', require $root . '/console/config/main-local.php'
    );
    $configuredCharset = (string) ($config['components']['db']['charset'] ?? 'unspecified');
    $config['components']['db']['class'] = yii\db\Connection::class;
    $config['components']['db']['commandClass'] = yii\db\Command::class;
    $config['components']['db']['enableSlaves'] = false;
    $config['components']['db']['enableSchemaCache'] = false;
    $config['components']['db']['enableLogging'] = false;
    $config['components']['db']['enableProfiling'] = false;
    $config['components']['db']['charset'] = 'utf8mb4';
    $config['components']['db']['attributes'][PDO::ATTR_EMULATE_PREPARES] = true;
    $config['components']['db']['attributes'][PDO::ATTR_TIMEOUT] = 10;
    $config['components']['authManager'] = ['class' => yii\rbac\DbManager::class];
    $config['components']['log'] = ['class' => yii\log\Dispatcher::class, 'targets' => []];
    $config['controllerMap'] = ['web-mcp-p2-migrate' => console\controllers\WebMcpP2MigrateController::class];
    $config['runtimePath'] = __DIR__ . '/runtime';
    if (!is_dir($config['runtimePath'])) mkdir($config['runtimePath'], 0700);
    Yii::setAlias('@console', __DIR__ . '/console');
    require __DIR__ . '/console/controllers/WebMcpP2MigrateController.php';
    require __DIR__ . '/api/modules/v1/services/PublicationArchive.php';
    $app = new yii\console\Application($config);
    $db = $app->db;
    if ($db->driverName !== 'mysql' || $db->createCommand('SELECT DATABASE()')->queryScalar() !== $expected) failP2('database_identity_mismatch');
    $current = captureP2($db, $expected, $root, $configuredCharset);
    $issues = readyP2($current);
    if ($phase === 'preflight') {
        $output = $current;
        unset($output['schemas'], $output['authItems'], $output['authChildren']);
        $output['readinessIssues'] = $issues;
        $output['releaseConfigMustUseUtf8mb4'] = true;
        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
        exit($issues === [] ? 0 : 1);
    }
    if ($phase === 'backup') {
        if (is_file($backupFile)) failP2('backup_already_exists');
        $handle = fopen($backupFile, 'x');
        if ($handle === false) failP2('backup_open_failed');
        $body = json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (fwrite($handle, $body) !== strlen($body)) failP2('backup_incomplete');
        fclose($handle);
        echo 'P2_BACKUP_READY ', $expected, ' SHA256=', hash_file('sha256', $backupFile), ' BYTES=', filesize($backupFile), PHP_EOL;
        exit(0);
    }
    if ($phase === 'plan') { runP2Action($app, 'plan'); exit(0); }
    if (!is_file($backupFile)) failP2('backup_required');
    $before = json_decode(file_get_contents($backupFile), true, 128, JSON_THROW_ON_ERROR);
    if ($before['database'] !== $expected || $before['serverFingerprint'] !== $current['serverFingerprint']) failP2('backup_target_mismatch');
    if ($phase === 'up') {
        if (!preg_match('/\A[a-f0-9]{64}\z/D', $argv[3]) || !hash_equals($argv[3], hash_file('sha256', $backupFile))) failP2('backup_digest_mismatch');
        if ($issues !== []) failP2('preflight_not_ready');
        runP2Action($app, 'up');
    }
    runP2Action($app, 'verify');
    $after = captureP2($db, $expected, $root, $configuredCharset);
    foreach ($before['authItems'] as $old) {
        $matches = array_values(array_filter($after['authItems'], static fn ($row) => $row['name'] === $old['name']));
        if ($matches !== [$old]) failP2('preexisting_rbac_item_changed');
    }
    foreach ($before['authChildren'] as $old) if (!in_array($old, $after['authChildren'], true)) failP2('preexisting_rbac_edge_changed');
    foreach ($before['migrationRows'] as $old) if (!in_array($old, $after['migrationRows'], true)) failP2('preexisting_migration_row_changed');
    echo 'P2_VERIFY_OK ', $expected, ' prior_rbac_and_migrations_preserved=1', PHP_EOL;
} catch (Throwable $error) {
    // Stable helper reasons contain no runtime inputs; other messages remain hidden.
    $reason = $error instanceof RuntimeException && preg_match('/\A[a-z0-9_:]+\z/D', $error->getMessage()) ? $error->getMessage() : get_class($error);
    fwrite(STDERR, 'P2_ERROR ' . $reason . PHP_EOL);
    exit(1);
}
