<?php
/** Disposable local MySQL + Redis only; no application database is selected. */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/test_bootstrap.php';
require_once dirname(__DIR__, 2) . '/api/modules/v1/components/LegacyArRefreshContinuity.php';

use api\modules\v1\models\User;
use api\modules\v1\services\LegacyArContinuityAdapter;
use api\modules\v1\services\SessionService;
use app\components\LegacyArRefreshContinuity as Engine;
use common\components\security\ServiceUnavailableHttpException;
use yii\db\Connection;
use yii\web\UnauthorizedHttpException;

set_exception_handler(static function (Throwable $exception): void {
    // Fixture identifiers only. Never output SQL parameters or response bodies.
    fwrite(STDERR, get_class($exception) . ': ' . $exception->getMessage() . "\n");
    exit(1);
});
$mysqlHost = getenv('AR_CONTINUITY_MYSQL_TEST_HOST') ?: '127.0.0.1';
$mysqlPort = (int)(getenv('AR_CONTINUITY_MYSQL_TEST_PORT') ?: 0);
$redisHost = getenv('AR_CONTINUITY_REDIS_TEST_HOST') ?: '127.0.0.1';
$redisPort = (int)(getenv('AR_CONTINUITY_REDIS_TEST_PORT') ?: 0);
foreach ([$mysqlHost, $redisHost] as $host) {
    if (!in_array($host, ['127.0.0.1', 'host.docker.internal'], true)) throw new RuntimeException('Local fixture host required.');
}
if ($mysqlPort < 1024 || $redisPort < 1024 || $mysqlPort > 65535 || $redisPort > 65535) {
    throw new RuntimeException('Explicit disposable fixture ports required.');
}
$admin = new PDO("mysql:host=$mysqlHost;port=$mysqlPort", 'root', getenv('AR_CONTINUITY_MYSQL_TEST_PASSWORD') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$database = 'codex_ar_continuity_php_' . bin2hex(random_bytes(6)) . '_test';
$admin->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db = new \common\components\CynosDbConnection(['dsn' => "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$database", 'username' => 'root',
    'password' => getenv('AR_CONTINUITY_MYSQL_TEST_PASSWORD') ?: '', 'charset' => 'utf8mb4', 'enableLogging' => false, 'enableProfiling' => false]);
$redis = new class(['hostname' => $redisHost, 'port' => $redisPort, 'database' => 14]) extends \yii\redis\Connection {
    public $beforeEval = null;
    public bool $loseNextEvalAcknowledgement = false;
    public int $evalCount = 0;
    public function executeCommand($name, $params = []) {
        if (strtoupper($name) === 'EVAL') {
            $this->evalCount++;
            if ($this->beforeEval !== null) ($this->beforeEval)();
            $result = parent::executeCommand($name, $params);
            if ($this->loseNextEvalAcknowledgement) {
                $this->loseNextEvalAcknowledgement = false;
                throw new RuntimeException('Disposable fixture lost acknowledgement.');
            }
            return $result;
        }
        return parent::executeCommand($name, $params);
    }
};
Yii::$app->set('db', $db);
Yii::$app->set('redis', $redis);
Yii::$app->set('deviceSnDb', $db);
putenv('AUTH_PROVIDER=legacy');
putenv('IDENTITY_DATA_SOURCE=legacy');
putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
// Test-only ephemeral key; never printed, persisted or passed to a deployment.
$binaryKey = random_bytes(32);
putenv('AR_REFRESH_REPLAY_KEY_BASE64=' . base64_encode($binaryKey));
$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    if (!$condition) throw new RuntimeException($message);
    $assertions++;
};
$denies = static function (callable $operation, int $status) use ($assert): void {
    try { $operation(); throw new LogicException('Expected rejection.'); }
    catch (\yii\web\HttpException $exception) { $assert($exception->statusCode === $status, 'Unexpected fail-closed status.'); }
};
try {
    $redis->open();
    $redis->executeCommand('FLUSHDB');
    $redis->executeCommand('HSET', [Engine::PREFIX . 'ready', 'v', '1', 'protocol', Engine::VERSION,
        'consumers', 'a1,server', 'reviewSha256', str_repeat('a', 64), 'state', 'ready']);
    $db->createCommand('CREATE TABLE user (id INT PRIMARY KEY, username VARCHAR(255), nickname VARCHAR(255),
        status SMALLINT NOT NULL, password_hash VARCHAR(255), auth_key VARCHAR(255)) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci')->execute();
    $db->createCommand('CREATE TABLE identity_legacy_security_versions (user_id INT PRIMARY KEY, epoch BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE device_sn (id INT PRIMARY KEY, user_id INT NULL, device_uuid VARCHAR(255), enabled TINYINT,
        activated_at DATETIME NULL) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE auth_assignment (item_name VARCHAR(64), user_id VARCHAR(64)) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TRIGGER fixture_security_update AFTER UPDATE ON user FOR EACH ROW BEGIN
        IF NOT(CAST(NEW.password_hash AS BINARY)<=>CAST(OLD.password_hash AS BINARY))
        OR NOT(CAST(NEW.auth_key AS BINARY)<=>CAST(OLD.auth_key AS BINARY)) OR NOT(NEW.status<=>OLD.status) THEN
        UPDATE identity_legacy_security_versions SET epoch=epoch+1 WHERE user_id=NEW.id; END IF; END')->execute();
    $db->createCommand()->insert('user', ['id' => 41, 'username' => 'fixture', 'nickname' => 'original', 'status' => 10,
        'password_hash' => 'fixture-hash', 'auth_key' => 'fixture-auth'])->execute();
    $db->createCommand()->insert('identity_legacy_security_versions', ['user_id' => 41, 'epoch' => 1])->execute();
    $db->schema->refresh();
    $user = static fn() => User::findOne(41);
    $sessions = new SessionService();
    $adapter = new LegacyArContinuityAdapter();
    $p = $sessions->issueToken($user(), ['ip' => '127.0.0.1', 'user_agent' => 'fixture']);
    $assert(strlen($p['refreshToken']) === 64, 'PHP raw refresh shape changed.');
    $metaKey = static fn(string $raw): string => Engine::PREFIX . 'byHash:' . hash('sha256', $raw);
    $id = (string)$redis->executeCommand('HGET', [$metaKey($p['refreshToken']), 'recordId']);
    $assert($redis->executeCommand('HGET', ['refresh_token:a:' . $id, 'key']) === hash('sha256', $p['refreshToken']), 'AR hash lookup changed.');
    $assert((int)$redis->executeCommand('PTTL', ['refresh_token:a:' . $id]) === -1, 'AR physical TTL changed.');
    $claims = Yii::$app->jwt->parse($p['accessToken'])->claims();
    $assert((int)$claims->get('uid') === 41, 'JWT integer UID changed.');
    $assert($claims->get('exp')->getTimestamp() - $claims->get('iat')->getTimestamp() === 10800, 'Access lifetime changed.');
    $s = $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'same-request']);
    $assert($s !== $p, 'Rotation did not issue successor.');
    $assert((int)$redis->executeCommand('EXISTS', ['refresh_token:a:' . $id]) === 0, 'PHP did not consume predecessor.');
    $assert($s === $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'same-request']), 'Original response was not replayed.');
    $assert($adapter->hasCredential($p['refreshToken']), 'Consumed predecessor lost QR fallback fence.');
    $denies(fn() => $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'another']), 401);
    $sessions->revokeRefreshToken($p['refreshToken']);
    $denies(fn() => $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'same-request']), 401);
    $denies(fn() => $sessions->refreshWithContinuity($s['refreshToken'], []), 401);
    $p = $sessions->issueToken($user());
    $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'aba']);
    // Byte-equivalent under unicode_ci, but security version must advance twice.
    $db->createCommand()->update('user', ['auth_key' => 'FIXTURE-AUTH'], ['id' => 41])->execute();
    $db->createCommand()->update('user', ['auth_key' => 'fixture-auth'], ['id' => 41])->execute();
    $denies(fn() => $sessions->refreshWithContinuity($p['refreshToken'], ['idempotency_key' => 'aba']), 401);
    $assert((string)$db->createCommand('SELECT CAST(epoch AS CHAR) FROM identity_legacy_security_versions WHERE user_id=41')->queryScalar() === '3', 'Byte-exact SQL epoch did not advance.');
    $p = $sessions->issueToken($user());
    $db->createCommand()->update('user', ['status' => 0], ['id' => 41])->execute();
    $db->createCommand()->update('user', ['status' => 10], ['id' => 41])->execute();
    $denies(fn() => $sessions->refreshWithContinuity($p['refreshToken'], []), 401);
    $stale = $user();
    $db->createCommand()->update('user', ['password_hash' => 'new-fixture-hash'], ['id' => 41])->execute();
    $before = (int)$redis->executeCommand('LLEN', ['refresh_token']);
    $denies(fn() => $sessions->issueToken($stale), 401);
    $assert((int)$redis->executeCommand('LLEN', ['refresh_token']) === $before, 'Stale login allocated a credential.');
    $p = $sessions->issueToken($user());
    $sessions->revokeUserSessions(41);
    $denies(fn() => $sessions->refreshWithContinuity($p['refreshToken'], []), 401);
    $db->createCommand('DELETE FROM identity_legacy_security_versions WHERE user_id=41')->execute();
    $denies(fn() => $sessions->issueToken($user()), 503);
    $assert((int)$redis->executeCommand('LLEN', ['refresh_token']) === 0, 'Missing ledger issued credentials.');
    $db->createCommand('INSERT INTO identity_legacy_security_versions VALUES (41,18446744073709551615)')->execute();
    $p = $sessions->issueToken($user());
    $assert($redis->executeCommand('HGET', [$metaKey($p['refreshToken']), 'accountEpoch']) === '18446744073709551615', 'UINT64 lost precision.');
    $denies(fn() => $sessions->consumeRefreshToken($p['refreshToken']), 503);
    $assert($sessions->revokeRefreshToken(null), 'Empty logout changed.');
    $assert(!$adapter->hasCredential('unknown-fixture-qr-code'), 'Unknown QR treated as AR.');
    $denies(fn() => $sessions->refreshWithContinuity('unreviewed-fixture-credential', []), 503);
    // The source SQL lock is held through the single Redis commit. A second
    // connection cannot change credentials while that commit is in progress.
    $competitor = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;dbname=$database", 'root', getenv('AR_CONTINUITY_MYSQL_TEST_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $competitor->exec('SET SESSION innodb_lock_wait_timeout=1');
    $redis->beforeEval = static function () use ($competitor, $db, $assert): void {
        $assert($db->maxRetries === 1, 'SQL retry suppression did not span Redis commit.');
        try {
            $competitor->exec("UPDATE user SET nickname='competing-writer' WHERE id=41");
            throw new LogicException('Concurrent SQL writer passed the credential lock.');
        } catch (PDOException $exception) {
            $assert((int)($exception->errorInfo[1] ?? 0) === 1205, 'Unexpected competing writer failure.');
        }
    };
    $lockToken = $sessions->issueToken($user());
    $redis->beforeEval = null;
    $assert($db->maxRetries === 3, 'SQL retry configuration was not restored.');
    $competitor->exec("UPDATE user SET nickname='after-lock' WHERE id=41");
    $assert($user()->nickname === 'after-lock', 'SQL lock remained after commit.');
    $count = $redis->evalCount;
    $redis->loseNextEvalAcknowledgement = true;
    $denies(fn() => $sessions->refreshWithContinuity($lockToken['refreshToken'], ['idempotency_key' => 'lost-ack']), 503);
    $assert($redis->evalCount === $count + 1, 'Lost acknowledgement retried the mutation internally.');
    $recovered = $sessions->refreshWithContinuity($lockToken['refreshToken'], ['idempotency_key' => 'lost-ack']);
    $assert($recovered === $sessions->refreshWithContinuity($lockToken['refreshToken'], ['idempotency_key' => 'lost-ack']), 'Lost acknowledgement did not recover the original response.');
    $assert($db->maxRetries === 3, 'Failure did not restore SQL retry configuration.');
    // A caller-owned transaction is neither committed nor rolled back here.
    $outer = $db->beginTransaction();
    $sessions->issueToken($user());
    $assert($outer->isActive, 'Adapter committed a caller transaction.');
    $outer->rollBack();
    // SN provenance is read from the predecessor. Public request fields cannot
    // remove it; every replay checks the current binding and account role.
    $db->createCommand()->insert('device_sn', ['id' => 7, 'user_id' => 41, 'device_uuid' => 'fixture-device', 'enabled' => 1,
        'activated_at' => '2026-01-01 00:00:00'])->execute();
    $snUser = $user();
    $snUser->authContext = ['auth_method' => 'device_sn', 'device_sn_id' => 7];
    $sn = $sessions->issueToken($snUser);
    $snResponse = $sessions->refreshWithContinuity($sn['refreshToken'], ['idempotency_key' => 'sn']);
    $snClaims = Yii::$app->jwt->parse($snResponse['accessToken'])->claims();
    $assert($snClaims->get('auth_method') === 'device_sn' && $snClaims->get('device_sn_id') === 7, 'SN JWT provenance changed.');
    $db->createCommand()->update('device_sn', ['enabled' => 0], ['id' => 7])->execute();
    $denies(fn() => $sessions->refreshWithContinuity($sn['refreshToken'], ['idempotency_key' => 'sn', 'auth_method' => null]), 401);
    $db->createCommand()->update('device_sn', ['enabled' => 1], ['id' => 7])->execute();
    $assert($snResponse === $sessions->refreshWithContinuity($sn['refreshToken'], ['idempotency_key' => 'sn']), 'Enabled valid SN could not replay.');
    $db->createCommand()->insert('auth_assignment', ['item_name' => 'admin', 'user_id' => '41'])->execute();
    $denies(fn() => $sessions->refreshWithContinuity($sn['refreshToken'], ['idempotency_key' => 'sn']), 401);
    $db->createCommand()->delete('auth_assignment')->execute();
    $db->createCommand()->update('device_sn', ['user_id' => null], ['id' => 7])->execute();
    $denies(fn() => $sessions->refreshWithContinuity($sn['refreshToken'], ['idempotency_key' => 'sn']), 401);
    // A1 permanent P accepted by PHP, then consumed; A1 must stop honoring it.
    $account = ['id' => 41, 'status' => 10, 'passwordHash' => 'new-fixture-hash', 'authKey' => 'fixture-auth', 'accountEpoch' => '18446744073709551615'];
    $a1 = new Engine(static fn($c, $a) => $redis->executeCommand($c, $a), $binaryKey, 'a1');
    $a = $a1->issue($account, static fn($raw) => ['refreshToken' => $raw]);
    $permanent = $a['refreshToken'];
    $a1->refresh($permanent, $account, '/v1/auth/refresh', 'a1', static fn($raw) => ['refreshToken' => $raw], ['issuer' => 'https://a1.fixture.invalid']);
    $php = $sessions->refreshWithContinuity($permanent, ['idempotency_key' => 'php']);
    $assert($php === $sessions->refreshWithContinuity($permanent, ['idempotency_key' => 'php']), 'Cross-consumer replay changed.');
    try {
        $a1->refresh($permanent, $account, '/v1/auth/refresh', 'a1', static fn($raw) => ['refreshToken' => $raw], ['issuer' => 'https://a1.fixture.invalid']);
        throw new LogicException('Consumed permanent predecessor revived.');
    } catch (RuntimeException $exception) { $assert($exception->getCode() === 401, 'A1 did not reject PHP-consumed predecessor.'); }
    echo json_encode(['passed' => true, 'assertions' => $assertions, 'realMysql' => true, 'realRedis' => true,
        'sourceDatabaseAccessed' => false, 'fixtureCleanedInFinally' => true], JSON_THROW_ON_ERROR), "\n";
} finally {
    $redis->executeCommand('FLUSHDB');
    $redis->close();
    $db->close();
    $admin->exec("DROP DATABASE `$database`");
}
