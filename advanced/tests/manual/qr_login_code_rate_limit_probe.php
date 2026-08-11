<?php

declare(strict_types=1);

/**
 * CLI-only companion for tools/identity/test-qr-login-code-rate-limit.mjs.
 *
 * It has two independent safety barriers: the Node runner allows only the
 * xrugc-test-* compose services, and this probe accepts only the isolated
 * Redis-only test environment. It creates a temporary user/RBAC schema only
 * when all of its tables are absent and drops only tables bearing this exact
 * comment. It never reads, writes, creates, or deletes user_linked.
 *
 * Inputs may include a short-lived test bearer or generated QR code. Those
 * values are accepted only over STDIN and are never included in output,
 * exceptions, logs, or command-line arguments.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

const QR_LOGIN_CODE_RATE_LIMIT_TABLE_COMMENT = 'qr_login_code_rate_limit_harness_v1';
const QR_LOGIN_CODE_RATE_LIMIT_ROUTE = '@restful/v1/tools/user-linked';
const QR_LOGIN_CODE_RATE_LIMIT_STATUS_ROUTE = '@restful/v1/tools/user-linked-status';

/** @param array<string, mixed> $payload */
function qrLoginCodeRateLimitEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

/** @param array<string, mixed> $payload */
function qrLoginCodeRateLimitWrite(array $payload): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    flush();
}

function qrLoginCodeRateLimitAssertIsolatedEnvironment(): void
{
    $expected = [
        'DEPLOYMENT_MODE' => 'test',
        'REDIS_HOST' => 'redis',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '15',
        'LOGIN_CODE_READ_MODE' => 'redis',
        'LOGIN_CODE_WRITE_MODE' => 'redis',
        'LOGIN_CODE_LEGACY_DB_AVAILABLE' => 'false',
        'MYSQL_HOST' => 'db',
    ];
    foreach ($expected as $name => $value) {
        if (getenv($name) !== $value) {
            throw new RuntimeException('This probe only runs in the isolated Redis-only test environment.');
        }
    }
}

/**
 * Input is intentionally one line. wait_consume keeps STDIN open after that
 * line to form an internal process barrier; all other actions receive EOF.
 *
 * @return array<string, mixed>
 */
function qrLoginCodeRateLimitInput(): array
{
    $line = fgets(STDIN);
    if (!is_string($line)) {
        throw new RuntimeException('Invalid probe input.');
    }

    $decoded = json_decode(trim($line), true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid probe input.');
    }

    $action = $decoded['action'] ?? null;
    $allowedActions = [
        'assert_configuration',
        'assert_database_mode_bypass',
        'assert_dual_mode_configuration',
        'setup_user',
        'cleanup_user',
        'mint_access_token',
        'clear_rate_state',
        'seed_rate_state',
        'inspect_rate_state',
        'consume',
        'wait_consume',
        'simulate_storage_failure',
    ];
    if (!is_string($action) || !in_array($action, $allowedActions, true)) {
        throw new RuntimeException('Invalid probe action.');
    }

    $result = ['action' => $action];
    if (in_array($action, [
        'setup_user',
        'cleanup_user',
        'mint_access_token',
        'clear_rate_state',
        'seed_rate_state',
        'inspect_rate_state',
        'consume',
        'wait_consume',
        'simulate_storage_failure',
    ], true)) {
        $userId = $decoded['user_id'] ?? null;
        if (!is_int($userId) || $userId < 100000000 || $userId > 2000000000) {
            throw new RuntimeException('Invalid probe user.');
        }
        $result['user_id'] = $userId;
    }

    if ($action === 'setup_user') {
        $username = $decoded['username'] ?? null;
        if (!is_string($username) || preg_match('/^[A-Za-z0-9_-]{16,64}$/D', $username) !== 1) {
            throw new RuntimeException('Invalid probe username.');
        }
        $result['username'] = $username;
    }

    if ($action === 'seed_rate_state') {
        $ages = $decoded['ages_ms'] ?? null;
        if (!is_array($ages) || count($ages) < 1 || count($ages) > 5) {
            throw new RuntimeException('Invalid rate-state seed.');
        }
        $normalizedAges = [];
        foreach ($ages as $age) {
            if (!is_int($age) || $age < 0 || $age > 120000) {
                throw new RuntimeException('Invalid rate-state seed.');
            }
            $normalizedAges[] = $age;
        }
        $result['ages_ms'] = $normalizedAges;
    }

    if ($action === 'inspect_rate_state') {
        $codes = $decoded['codes'] ?? [];
        if (!is_array($codes) || count($codes) > 10) {
            throw new RuntimeException('Invalid state inspection.');
        }
        foreach ($codes as $code) {
            if (!is_string($code) || preg_match('/^[A-Za-z0-9_-]{64}$/D', $code) !== 1) {
                throw new RuntimeException('Invalid state inspection.');
            }
        }
        $result['codes'] = array_values($codes);
    }

    return $result;
}

function qrLoginCodeRateLimitDatabase(): PDO
{
    $host = getenv('MYSQL_HOST');
    $database = getenv('MYSQL_DB');
    $username = getenv('MYSQL_USERNAME');
    $password = getenv('MYSQL_PASSWORD');
    if (!is_string($host) || !is_string($database) || !is_string($username) || !is_string($password) || $database === '') {
        throw new RuntimeException('Test database configuration is unavailable.');
    }

    return new PDO(
        'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4',
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
}

/** @return array<string, ?string> */
function qrLoginCodeRateLimitHarnessTableComments(PDO $database): array
{
    $tableNames = ['user', 'auth_rule', 'auth_item', 'auth_item_child', 'auth_assignment'];
    $placeholders = implode(', ', array_fill(0, count($tableNames), '?'));
    $statement = $database->prepare(
        'SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES '
        . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')'
    );
    $statement->execute($tableNames);

    $comments = array_fill_keys($tableNames, null);
    while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
        $tableName = $row['TABLE_NAME'] ?? null;
        $comment = $row['TABLE_COMMENT'] ?? null;
        if (is_string($tableName) && array_key_exists($tableName, $comments)) {
            $comments[$tableName] = is_string($comment) ? $comment : '';
        }
    }

    return $comments;
}

function qrLoginCodeRateLimitSetupUser(PDO $database, int $userId, string $username): void
{
    foreach (qrLoginCodeRateLimitHarnessTableComments($database) as $comment) {
        if ($comment !== null) {
            throw new RuntimeException('The isolated test database is not empty for this harness.');
        }
    }

    $comment = QR_LOGIN_CODE_RATE_LIMIT_TABLE_COMMENT;
    $database->exec(
        'CREATE TABLE `user` ('
        . '`id` INT NOT NULL, '
        . '`username` VARCHAR(255) NULL, '
        . '`auth_key` VARCHAR(32) NULL, '
        . "`password_hash` VARCHAR(255) NOT NULL DEFAULT '', "
        . '`password_reset_token` VARCHAR(255) NULL, '
        . '`email` VARCHAR(255) NULL, '
        . '`status` SMALLINT NOT NULL DEFAULT 10, '
        . '`created_at` INT NULL, '
        . '`updated_at` INT NULL, '
        . '`verification_token` VARCHAR(255) NULL, '
        . '`access_token` VARCHAR(255) NULL, '
        . '`wx_openid` VARCHAR(255) NULL, '
        . '`nickname` VARCHAR(255) NULL, '
        . '`email_verified_at` INT NULL, '
        . 'PRIMARY KEY (`id`), '
        . 'UNIQUE KEY `uq_qr_login_code_rate_limit_username` (`username`)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
        . "COMMENT='" . $comment . "'"
    );
    $database->exec(
        'CREATE TABLE `auth_rule` ('
        . '`name` VARCHAR(64) NOT NULL, `data` BLOB NULL, `created_at` INT NULL, `updated_at` INT NULL, '
        . 'PRIMARY KEY (`name`)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
        . "COMMENT='" . $comment . "'"
    );
    $database->exec(
        'CREATE TABLE `auth_item` ('
        . '`name` VARCHAR(64) NOT NULL, `type` SMALLINT NOT NULL, `description` TEXT NULL, '
        . '`rule_name` VARCHAR(64) NULL, `data` BLOB NULL, `created_at` INT NULL, `updated_at` INT NULL, '
        . 'PRIMARY KEY (`name`), KEY `idx_qr_login_code_rate_limit_rule_name` (`rule_name`)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
        . "COMMENT='" . $comment . "'"
    );
    $database->exec(
        'CREATE TABLE `auth_item_child` ('
        . '`parent` VARCHAR(64) NOT NULL, `child` VARCHAR(64) NOT NULL, '
        . 'PRIMARY KEY (`parent`, `child`), KEY `idx_qr_login_code_rate_limit_child` (`child`)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
        . "COMMENT='" . $comment . "'"
    );
    $database->exec(
        'CREATE TABLE `auth_assignment` ('
        . '`item_name` VARCHAR(64) NOT NULL, `user_id` VARCHAR(64) NOT NULL, `created_at` INT NULL, '
        . 'PRIMARY KEY (`item_name`, `user_id`), KEY `idx_qr_login_code_rate_limit_user_id` (`user_id`)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
        . "COMMENT='" . $comment . "'"
    );

    $statement = $database->prepare(
        'INSERT INTO `user` (`id`, `username`, `nickname`, `status`) '
        . 'VALUES (:id, :username, :nickname, 10)'
    );
    $statement->execute([
        ':id' => $userId,
        ':username' => $username,
        ':nickname' => 'qr-rate-limit-test',
    ]);
    $itemStatement = $database->prepare(
        'INSERT INTO `auth_item` (`name`, `type`, `description`) VALUES (:name, 2, :description)'
    );
    $assignmentStatement = $database->prepare(
        'INSERT INTO `auth_assignment` (`item_name`, `user_id`) VALUES (:itemName, :userId)'
    );
    foreach ([QR_LOGIN_CODE_RATE_LIMIT_ROUTE, QR_LOGIN_CODE_RATE_LIMIT_STATUS_ROUTE] as $route) {
        $itemStatement->execute([
            ':name' => $route,
            ':description' => 'isolated test-only route permission',
        ]);
        $assignmentStatement->execute([
            ':itemName' => $route,
            ':userId' => (string)$userId,
        ]);
    }
}

function qrLoginCodeRateLimitCleanupUser(PDO $database, int $userId): void
{
    $comments = qrLoginCodeRateLimitHarnessTableComments($database);
    $present = array_filter($comments, static fn (?string $comment): bool => $comment !== null);
    if ($present === []) {
        return;
    }
    if (count($present) !== count($comments)) {
        throw new RuntimeException('The isolated test schema was not created by this harness.');
    }
    foreach ($comments as $comment) {
        if (!is_string($comment) || !hash_equals(QR_LOGIN_CODE_RATE_LIMIT_TABLE_COMMENT, $comment)) {
            throw new RuntimeException('The isolated test schema was not created by this harness.');
        }
    }

    $statement = $database->prepare('DELETE FROM `user` WHERE `id` = :id');
    $statement->execute([':id' => $userId]);
    // auth_* has no foreign-key constraints in the temporary schema, but the
    // order remains child-first to match the normal Yii RBAC schema.
    foreach (['auth_assignment', 'auth_item_child', 'auth_item', 'auth_rule', 'user'] as $tableName) {
        $database->exec('DROP TABLE `' . $tableName . '`');
    }
}

function qrLoginCodeRateLimitBootstrapApp(?array $loginCodeOverride = null): yii\web\Application
{
    $basePath = dirname(__DIR__, 2);
    defined('YII_DEBUG') or define('YII_DEBUG', false);
    defined('YII_ENV') or define('YII_ENV', 'test');
    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';
    require_once $basePath . '/api/config/bootstrap.php';

    $config = yii\helpers\ArrayHelper::merge(
        require $basePath . '/common/config/main.php',
        require $basePath . '/common/config/main-local.php',
        require $basePath . '/api/config/main.php',
        require $basePath . '/api/config/main-local.php',
    );
    if ($loginCodeOverride !== null) {
        $config['params']['loginCode'] = $loginCodeOverride;
    }
    $config['id'] = 'qr-login-code-rate-limit-probe';

    return new yii\web\Application($config);
}

/** @return common\components\security\RateLimiter */
function qrLoginCodeRateLimitDedicatedLimiter(): common\components\security\RateLimiter
{
    $limiter = Yii::$app->get('loginCodeIssueRateLimiter');
    if (!$limiter instanceof common\components\security\RateLimiter) {
        throw new RuntimeException('The dedicated limiter is unavailable.');
    }

    return $limiter;
}

function qrLoginCodeRateLimitKey(int $userId): string
{
    // RateLimiter::buildKey() appends strategy first, then identifier.
    return 'auth:login-code:v1:issue-rate:user-linked-issue:user_' . $userId;
}

function qrLoginCodeRateLimitRedis(): yii\redis\Connection
{
    $redis = Yii::$app->get('redis');
    if (!$redis instanceof yii\redis\Connection) {
        throw new RuntimeException('The isolated Redis connection is unavailable.');
    }

    return $redis;
}

/** @return array{allowed: bool, remaining: int, reset_at: int, retry_after: int} */
function qrLoginCodeRateLimitConsume(int $userId): array
{
    $result = qrLoginCodeRateLimitDedicatedLimiter()->consume('user_' . $userId, 'user-linked-issue');
    if (
        !is_array($result)
        || !is_bool($result['allowed'] ?? null)
        || !is_int($result['remaining'] ?? null)
        || !is_int($result['reset_at'] ?? null)
        || !is_int($result['retry_after'] ?? null)
    ) {
        throw new RuntimeException('Unexpected limiter result.');
    }

    return $result;
}

function qrLoginCodeRateLimitSeedState(int $userId, array $ages): int
{
    $redis = qrLoginCodeRateLimitRedis();
    $key = qrLoginCodeRateLimitKey($userId);
    $time = $redis->executeCommand('TIME');
    if (!is_array($time) || !isset($time[0], $time[1]) || !ctype_digit((string)$time[0]) || !ctype_digit((string)$time[1])) {
        throw new RuntimeException('The isolated Redis clock is unavailable.');
    }
    $nowMs = ((int)$time[0] * 1000) + intdiv((int)$time[1], 1000);

    $redis->executeCommand('DEL', [$key]);
    foreach ($ages as $index => $age) {
        $score = $nowMs - $age;
        $member = $score . ':' . bin2hex(random_bytes(16)) . ':' . $index;
        $redis->executeCommand('ZADD', [$key, $score, $member]);
    }
    $redis->executeCommand('PEXPIRE', [$key, 60000]);

    return count($ages);
}

/** @param mixed $value */
function qrLoginCodeRateLimitIntegerReply($value): ?int
{
    if (is_int($value)) {
        return $value;
    }
    if (!is_string($value) || preg_match('/^-?(?:0|[1-9]\\d*)$/D', $value) !== 1) {
        return null;
    }
    $unsigned = $value[0] === '-' ? substr($value, 1) : $value;
    $maximum = (string)PHP_INT_MAX;
    if (strlen($unsigned) > strlen($maximum) || (strlen($unsigned) === strlen($maximum) && strcmp($unsigned, $maximum) > 0)) {
        return null;
    }

    return (int)$value;
}

/** @param list<string> $codes
 * @return array{type: string, count: int, ttl_ms: int, members_safe: bool, separate_from_code_record: bool}
 */
function qrLoginCodeRateLimitInspectState(int $userId, array $codes): array
{
    $redis = qrLoginCodeRateLimitRedis();
    $key = qrLoginCodeRateLimitKey($userId);
    $type = $redis->executeCommand('TYPE', [$key]);
    $count = qrLoginCodeRateLimitIntegerReply($redis->executeCommand('ZCARD', [$key]));
    $ttl = qrLoginCodeRateLimitIntegerReply($redis->executeCommand('PTTL', [$key]));
    $members = $redis->executeCommand('ZRANGE', [$key, 0, -1]);
    if (!is_string($type) || $count === null || $ttl === null || !is_array($members)) {
        throw new RuntimeException('The isolated rate state could not be inspected.');
    }

    $membersSafe = true;
    $separateFromCodeRecord = $type === 'zset';
    foreach ($members as $member) {
        if (!is_string($member) || preg_match('/^\\d+:[a-f0-9]{32}(?::\\d+)?$/D', $member) !== 1) {
            $membersSafe = false;
        }
        foreach ($codes as $code) {
            $digest = hash('sha256', $code);
            if (str_contains($member, $code) || str_contains($member, $digest)) {
                $separateFromCodeRecord = false;
            }
        }
    }

    return [
        'type' => $type,
        'count' => $count,
        'ttl_ms' => $ttl,
        'members_safe' => $membersSafe,
        'separate_from_code_record' => $separateFromCodeRecord,
    ];
}

/** @return array<string, mixed> */
function qrLoginCodeRateLimitAssertConfiguration(): array
{
    qrLoginCodeRateLimitBootstrapApp();
    $dedicated = qrLoginCodeRateLimitDedicatedLimiter();
    $global = Yii::$app->get('rateLimiter');
    if (!$global instanceof common\components\security\RateLimiter) {
        throw new RuntimeException('The legacy limiter is unavailable.');
    }

    $controller = new api\modules\v1\controllers\ToolsController('tools', null);
    $behaviors = $controller->behaviors();
    $issueBehavior = $behaviors['loginCodeIssueRateLimiter'] ?? null;
    $statusBypassesLimiter = is_array($issueBehavior)
        && ($issueBehavior['only'] ?? null) === ['user-linked']
        && ($issueBehavior['atomicConsume'] ?? null) === true;
    $dedicatedStrategy = $dedicated->getStrategy('user-linked-issue');

    return [
        'ok' => true,
        'operation' => 'assert_configuration',
        'dedicated_redis_storage' => $dedicated->getStorage() instanceof common\components\security\RedisSlidingWindowRateLimiterStorage,
        'dedicated_strategy' => $dedicatedStrategy === ['limit' => 5, 'window' => 60],
        'status_bypasses_limiter' => $statusBypassesLimiter,
        'legacy_strategies_unchanged' => $global->getStrategy('ip') === ['limit' => 100, 'window' => 60]
            && $global->getStrategy('user') === ['limit' => 1000, 'window' => 3600]
            && $global->getStrategy('login') === ['limit' => 5, 'window' => 900],
    ];
}

/** @return array<string, mixed> */
function qrLoginCodeRateLimitAssertDatabaseModeBypass(): array
{
    qrLoginCodeRateLimitBootstrapApp([
        'readMode' => 'database',
        'writeMode' => 'database',
        'prefix' => 'auth:login-code:v1',
        'protocolFingerprint' => '8cb6c5546bf71994bca800431fc986bc68a35561942480840395f0df168a061a',
        'activeWindowSeconds' => 60,
        'recordRetentionSeconds' => 300,
        'issueLimit' => 5,
        'issueWindowSeconds' => 60,
        'legacyDbAvailable' => true,
    ]);
    $controller = new api\modules\v1\controllers\ToolsController('tools', null);
    $behaviors = $controller->behaviors();

    return [
        'ok' => true,
        'operation' => 'assert_database_mode_bypass',
        'no_issue_limiter' => !array_key_exists('loginCodeIssueRateLimiter', $behaviors),
        'no_login_code_readiness' => !array_key_exists('loginCodeReadiness', $behaviors),
    ];
}

/**
 * Build a real API application using only an in-process mode override. This
 * deliberately does not call an endpoint, LoginCodeStore, MySQL, or Redis;
 * it proves both supported dual-mode pairs attach the exact production
 * RateLimitBehavior to ToolsController and resolve the dedicated atomic
 * Redis-backed limiter rather than the legacy global component.
 *
 * @return array{attached: bool, dedicated_strategy: bool, dedicated_storage: bool, readiness_attached: bool}
 */
function qrLoginCodeRateLimitDualModeBehavior(string $readMode): array
{
    qrLoginCodeRateLimitBootstrapApp([
        'readMode' => $readMode,
        'writeMode' => 'dual',
        'prefix' => 'auth:login-code:v1',
        'protocolFingerprint' => '8cb6c5546bf71994bca800431fc986bc68a35561942480840395f0df168a061a',
        'activeWindowSeconds' => 60,
        'recordRetentionSeconds' => 300,
        'issueLimit' => 5,
        'issueWindowSeconds' => 60,
        'legacyDbAvailable' => true,
    ]);
    $controller = new api\modules\v1\controllers\ToolsController('tools', null);
    $behavior = $controller->getBehavior('loginCodeIssueRateLimiter');
    $readiness = $controller->getBehavior('loginCodeReadiness');
    $limiter = qrLoginCodeRateLimitDedicatedLimiter();

    return [
        'attached' => $behavior instanceof common\components\security\RateLimitBehavior
            && $behavior->rateLimiter === 'loginCodeIssueRateLimiter'
            && $behavior->defaultStrategy === 'user-linked-issue'
            && $behavior->atomicConsume === true
            && $behavior->only === ['user-linked'],
        'dedicated_strategy' => $limiter->getStrategy('user-linked-issue') === ['limit' => 5, 'window' => 60],
        'dedicated_storage' => $limiter->getStorage() instanceof common\components\security\RedisSlidingWindowRateLimiterStorage,
        'readiness_attached' => $readiness instanceof api\modules\v1\filters\LoginCodeReadinessBehavior,
    ];
}

/** @return array<string, mixed> */
function qrLoginCodeRateLimitAssertDualModeConfiguration(): array
{
    $databaseDual = qrLoginCodeRateLimitDualModeBehavior('database');
    $redisFirstDual = qrLoginCodeRateLimitDualModeBehavior('redis-first');

    return [
        'ok' => true,
        'operation' => 'assert_dual_mode_configuration',
        'database_dual' => $databaseDual,
        'redis_first_dual' => $redisFirstDual,
    ];
}

/** @return array<string, mixed> */
function qrLoginCodeRateLimitMintAccessToken(int $userId): array
{
    qrLoginCodeRateLimitBootstrapApp();
    $user = api\modules\v1\models\User::findIdentity($userId);
    if (!$user instanceof api\modules\v1\models\User) {
        throw new RuntimeException('The temporary test user is unavailable.');
    }
    // A CLI Request does not infer a public host. The actual HTTP service
    // validates only the signature/time claims, while the test bearer needs a
    // syntactically valid issuer/audience to exercise its authenticated route.
    Yii::$app->request->setHostInfo('http://127.0.0.1:8091');
    $token = $user->generateAccessToken();
    if (!is_string($token) || strlen($token) < 32 || preg_match('/\\s/D', $token) === 1) {
        throw new RuntimeException('The temporary bearer could not be issued.');
    }

    // This is the sole probe response which contains a test bearer. The Node
    // runner retains it only in memory and never emits it in its report.
    return ['ok' => true, 'operation' => 'mint_access_token', 'access_token' => $token];
}

/** @return array<string, mixed> */
function qrLoginCodeRateLimitSimulateStorageFailure(int $userId): array
{
    qrLoginCodeRateLimitBootstrapApp();
    $failingRedis = new yii\redis\Connection([
        'hostname' => '127.0.0.1',
        'port' => 1,
        'database' => 15,
    ]);
    $storage = new common\components\security\RedisSlidingWindowRateLimiterStorage([
        'redis' => $failingRedis,
    ]);
    $limiter = new common\components\security\RateLimiter([
        'keyPrefix' => 'auth:login-code:v1:issue-rate:',
        'strategies' => ['user-linked-issue' => ['limit' => 5, 'window' => 60]],
    ]);
    $limiter->setStorage($storage);

    $user = api\modules\v1\models\User::findIdentity($userId);
    if (!$user instanceof api\modules\v1\models\User) {
        throw new RuntimeException('The temporary test user is unavailable.');
    }
    Yii::$app->user->setIdentity($user);
    $behavior = new common\components\security\RateLimitBehavior([
        'rateLimiter' => $limiter,
        'defaultStrategy' => 'user-linked-issue',
        'atomicConsume' => true,
    ]);
    $controller = new yii\web\Controller('rate-limit-probe', null);
    $action = new yii\base\InlineAction('user-linked', $controller, 'actionIndex');

    try {
        $behavior->beforeAction($action);
    } catch (common\components\security\ServiceUnavailableHttpException $exception) {
        return [
            'ok' => true,
            'operation' => 'simulate_storage_failure',
            'fail_closed' => true,
            'http_status' => $exception->statusCode,
        ];
    }

    throw new RuntimeException('The rate limiter did not fail closed.');
}

try {
    qrLoginCodeRateLimitAssertIsolatedEnvironment();
    $input = qrLoginCodeRateLimitInput();
    $action = $input['action'];

    if ($action === 'assert_configuration') {
        qrLoginCodeRateLimitEmit(qrLoginCodeRateLimitAssertConfiguration());
    }
    if ($action === 'assert_database_mode_bypass') {
        qrLoginCodeRateLimitEmit(qrLoginCodeRateLimitAssertDatabaseModeBypass());
    }
    if ($action === 'assert_dual_mode_configuration') {
        qrLoginCodeRateLimitEmit(qrLoginCodeRateLimitAssertDualModeConfiguration());
    }
    if ($action === 'setup_user') {
        qrLoginCodeRateLimitSetupUser(qrLoginCodeRateLimitDatabase(), $input['user_id'], $input['username']);
        qrLoginCodeRateLimitEmit(['ok' => true, 'operation' => 'setup_user']);
    }
    if ($action === 'cleanup_user') {
        qrLoginCodeRateLimitCleanupUser(qrLoginCodeRateLimitDatabase(), $input['user_id']);
        qrLoginCodeRateLimitEmit(['ok' => true, 'operation' => 'cleanup_user']);
    }
    if ($action === 'mint_access_token') {
        qrLoginCodeRateLimitEmit(qrLoginCodeRateLimitMintAccessToken($input['user_id']));
    }
    if ($action === 'simulate_storage_failure') {
        // Replace only this probe's limiter client; the test Redis service
        // remains running for every other isolated verification.
        qrLoginCodeRateLimitEmit(qrLoginCodeRateLimitSimulateStorageFailure($input['user_id']));
    }

    qrLoginCodeRateLimitBootstrapApp();
    if ($action === 'clear_rate_state') {
        qrLoginCodeRateLimitRedis()->executeCommand('DEL', [qrLoginCodeRateLimitKey($input['user_id'])]);
        qrLoginCodeRateLimitEmit(['ok' => true, 'operation' => 'clear_rate_state']);
    }
    if ($action === 'seed_rate_state') {
        qrLoginCodeRateLimitEmit([
            'ok' => true,
            'operation' => 'seed_rate_state',
            'count' => qrLoginCodeRateLimitSeedState($input['user_id'], $input['ages_ms']),
        ]);
    }
    if ($action === 'inspect_rate_state') {
        qrLoginCodeRateLimitEmit([
            'ok' => true,
            'operation' => 'inspect_rate_state',
            ...qrLoginCodeRateLimitInspectState($input['user_id'], $input['codes']),
        ]);
    }
    if ($action === 'consume') {
        qrLoginCodeRateLimitEmit([
            'ok' => true,
            'operation' => 'consume',
            ...qrLoginCodeRateLimitConsume($input['user_id']),
        ]);
    }
    if ($action === 'wait_consume') {
        qrLoginCodeRateLimitWrite(['ok' => true, 'operation' => 'ready']);
        $go = fgets(STDIN);
        if (!is_string($go) || trim($go) !== 'go') {
            throw new RuntimeException('The isolated worker barrier was not released.');
        }
        qrLoginCodeRateLimitEmit([
            'ok' => true,
            'operation' => 'consume',
            ...qrLoginCodeRateLimitConsume($input['user_id']),
        ]);
    }

    throw new RuntimeException('Invalid probe action.');
} catch (Throwable) {
    qrLoginCodeRateLimitEmit(['ok' => false, 'error' => 'probe_failed'], 1);
}
