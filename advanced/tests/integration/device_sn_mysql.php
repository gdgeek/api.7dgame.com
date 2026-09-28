<?php
/**
 * Dedicated disposable MySQL required. Never points at the application's MYSQL_DB.
 * DEVICE_SN_MYSQL_TEST_PORT=13318 php tests/integration/device_sn_mysql.php
 */
require dirname(__DIR__, 2) . '/test_bootstrap.php';

// Yii's console error handler can otherwise report an uncaught test failure with exit 0.
set_exception_handler(static function (Throwable $exception): void {
    fwrite(STDERR, (string)$exception . "\n");
    exit(1);
});

use api\modules\v1\services\DeviceSnCredential;
use api\modules\v1\services\DeviceSnService;
use yii\db\Connection;

$port = filter_var(getenv('DEVICE_SN_MYSQL_TEST_PORT'), FILTER_VALIDATE_INT);
if (!$port || $port < 1024 || $port > 65535) {
    fwrite(STDERR, "Set DEVICE_SN_MYSQL_TEST_PORT to a disposable local MySQL port.\n");
    exit(2);
}
$baseDsn = 'mysql:host=127.0.0.1;port=' . $port;
$admin = new PDO($baseDsn, 'root', getenv('DEVICE_SN_MYSQL_TEST_PASSWORD') ?: '');
$database = 'codex_device_sn_php_' . bin2hex(random_bytes(5));
$admin->exec('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$admin = null;
$config = ['dsn' => $baseDsn . ';dbname=' . $database,
    'username' => 'root', 'password' => getenv('DEVICE_SN_MYSQL_TEST_PASSWORD') ?: '', 'charset' => 'utf8mb4',
    'commandClass' => \api\modules\v1\components\DeviceSnCommand::class, 'enableLogging' => false, 'enableProfiling' => false];
$key = base64_encode(random_bytes(32));
$db = new Connection($config);
Yii::$app->set('db', $db);
Yii::$app->set('deviceSnDb', $db);
Yii::$app->set('authManager', new \yii\rbac\DbManager(['db' => $db]));
$assertions = 0;
$assert = function ($condition, string $message) use (&$assertions) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $assertions++;
};
$migration = null;
try {
    $db->createCommand('CREATE TABLE user (id INT PRIMARY KEY, username VARCHAR(255), nickname VARCHAR(255), status INT,
        password_hash VARCHAR(255), auth_key VARCHAR(32), password_reset_token VARCHAR(255), email VARCHAR(255),
        created_at INT, updated_at INT, email_verified_at INT) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE user_info (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, info JSON, avatar_id INT, gold INT, points INT) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE auth_item (name VARCHAR(64) PRIMARY KEY, type INT, description TEXT, rule_name VARCHAR(64), data BLOB, created_at INT, updated_at INT) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE auth_assignment (item_name VARCHAR(64), user_id VARCHAR(64), created_at INT, PRIMARY KEY(item_name,user_id)) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE auth_item_child (parent VARCHAR(64), child VARCHAR(64), PRIMARY KEY(parent,child), FOREIGN KEY (parent) REFERENCES auth_item(name), FOREIGN KEY (child) REFERENCES auth_item(name)) ENGINE=InnoDB')->execute();
    $db->createCommand('CREATE TABLE audit_log (id INT AUTO_INCREMENT PRIMARY KEY, event_type VARCHAR(50), user_id INT, ip_address VARCHAR(45), action VARCHAR(100), resource VARCHAR(255), context JSON, created_at TIMESTAMP) ENGINE=InnoDB')->execute();
    $db->createCommand()->batchInsert('user', ['id', 'username', 'nickname', 'status'], [[1, 'root', 'root', 10], [2, 'player', 'player', 10], [3, 'other', 'other', 10]])->execute();
    foreach (['root', 'user', 'admin'] as $role) {
        $db->createCommand()->insert('auth_item', ['name' => $role, 'type' => 1])->execute();
    }
    $db->createCommand()->batchInsert('auth_assignment', ['item_name', 'user_id'], [['root', '1'], ['user', '2'], ['user', '3']])->execute();
    require dirname(__DIR__, 2) . '/console/migrations/m260926_210000_create_device_sn_table.php';
    ob_start();
    $migration = new m260926_210000_create_device_sn_table(['db' => $db]);
    $migration->safeUp();
    ob_end_clean();
    $assert(!in_array('device', $db->schema->getTableNames('', true), true), 'Legacy device table must not be required.');
    $snSchema = $db->schema->getTableSchema('device_sn', true);
    $assert(isset($snSchema->columns['device_uuid']) && $snSchema->columns['device_uuid']->allowNull,
        'The SN table must directly store a nullable device UUID.');
    $assert(!isset($snSchema->columns['device_id']), 'The SN table must not reference a legacy device ID.');
    $service = new DeviceSnService($db, new DeviceSnCredential(['test' => $key], 'test'));
    $assert(count($service->accounts('', 1, 20)['items']) === 2, 'Account query must work with real MySQL collations.');
    [$first, $second, $third, $fourth] = $service->generate(2, 4, 'integration', 1);
    foreach ([$first, $second, $third, $fourth] as $generated) {
        $assert(preg_match('/^[0-9A-HJKMNP-TV-Z]{4}(?:-[0-9A-HJKMNP-TV-Z]{4}){3}$/D', $generated['sn']) === 1,
            'New credentials must contain exactly sixteen characters in four groups.');
    }

    // Independent processes/connections race against the same InnoDB indexes.
    $race = function (array $attempts) use ($config, $key, &$db) {
        $db->close();
        $children = [];
        foreach ($attempts as [$sn, $uuid]) {
            $pipes = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            $pid = pcntl_fork();
            if ($pid === 0) {
                fclose($pipes[0]);
                $childDb = new Connection($config);
                Yii::$app->set('db', $childDb);
                Yii::$app->set('deviceSnDb', $childDb);
                Yii::$app->set('authManager', new \yii\rbac\DbManager(['db' => $childDb]));
                (new ReflectionProperty(\mdm\admin\components\Configs::class, '_instance'))->setValue(null, null);
                fread($pipes[1], 1);
                try {
                    (new DeviceSnService($childDb, new DeviceSnCredential(['test' => $key], 'test')))->authenticate($sn, $uuid, true);
                    fwrite($pipes[1], '200');
                } catch (\yii\web\HttpException $exception) {
                    fwrite($pipes[1], (string)$exception->statusCode);
                } catch (Throwable $exception) {
                    fwrite($pipes[1], 'error:' . get_class($exception));
                }
                fclose($pipes[1]);
                exit(0);
            }
            fclose($pipes[1]);
            $children[] = [$pid, $pipes[0]];
        }
        foreach ($children as [, $pipe]) {
            fwrite($pipe, 'x');
        }
        $results = [];
        foreach ($children as [$pid, $pipe]) {
            $results[] = stream_get_contents($pipe);
            fclose($pipe);
            pcntl_waitpid($pid, $status);
        }
        $db->open();
        return $results;
    };
    $results = $race([[$first['sn'], 'device-one'], [$first['sn'], 'device-two']]);
    sort($results);
    $assert($results === ['200', '409'], 'Same SN race must have one winner: ' . json_encode($results));
    $results = $race([[$second['sn'], 'shared-device'], [$third['sn'], 'shared-device']]);
    sort($results);
    $assert($results === ['200', '409'], 'Same device race must have one winner: ' . json_encode($results));
    $results = $race([[$fourth['sn'], 'idempotent'], [$fourth['sn'], 'idempotent']]);
    $assert($results === ['200', '200'], 'Same-pair retries must both succeed.');
    $assert((int)$db->createCommand('SELECT COUNT(DISTINCT device_uuid) FROM device_sn WHERE device_uuid IS NOT NULL')->queryScalar() === 3, 'Exactly three device bindings expected.');
    $assert((int)$db->createCommand('SELECT COUNT(*) FROM device_sn WHERE (device_uuid IS NULL) <> (activated_at IS NULL)')->queryScalar() === 0,
        'Racing activation must set the UUID and activation time atomically.');

    // Persist the pre-shortening encryption format without calling the new generator.
    // Cover both an unused distributed code and an existing device binding.
    $legacyCodes = [];
    foreach (['pending' => null, 'active' => 'legacy-existing-device'] as $state => $legacyUuid) {
        $raw = str_repeat($state === 'pending' ? 'ABCDEFGH' : 'JKMNPQRS', 4);
        $hash = hash('sha256', $raw);
        $nonce = random_bytes(12);
        $tag = '';
        $encrypted = openssl_encrypt($raw, 'aes-256-gcm', base64_decode($key, true), OPENSSL_RAW_DATA,
            $nonce, $tag, 'device-sn:v1:test:' . $hash, 16);
        $assert(is_string($encrypted) && strlen($nonce . $tag . $encrypted) === 60,
            'Legacy fixture must use the original 32-character ciphertext format.');
        $ciphertext = base64_encode($nonce . $tag . $encrypted);
        $db->createCommand()->insert('device_sn', [
            'user_id' => 2, 'sn_hash' => $hash, 'sn_ciphertext' => $ciphertext, 'key_id' => 'test',
            'sn_tail' => substr($raw, -4), 'device_uuid' => $legacyUuid, 'enabled' => 1,
            'created_by' => 1, 'created_at' => '2026-09-26 00:00:00', 'updated_at' => '2026-09-26 00:00:00',
            'activated_at' => $legacyUuid === null ? null : '2026-09-26 00:01:00', 'remark' => 'legacy fixture',
        ])->execute();
        $id = (int)$db->getLastInsertID();
        $formatted = implode('-', str_split($raw, 4));
        $legacyCodes[$state] = ['id' => $id, 'sn' => $formatted];
        $uuid = $legacyUuid ?? 'legacy-new-device';
        $authorized = $service->authenticate(strtolower($formatted), $uuid, $legacyUuid === null);
        $assert($authorized['device_sn_id'] === $id, 'Legacy code must activate or log in using its original hash.');
        $assert($service->authenticate($formatted, $uuid, false)['device_sn_id'] === $id,
            'Legacy code must continue to log in without shortening or rebinding.');
        $assert($service->reveal($id, 1)['sn'] === $formatted, 'Legacy reveal must preserve all eight groups.');
        $stored = $db->createCommand('SELECT sn_hash, sn_ciphertext, device_uuid FROM device_sn WHERE id=:id', [':id' => $id])->queryOne();
        $assert($stored['sn_hash'] === $hash && $stored['sn_ciphertext'] === $ciphertext && $stored['device_uuid'] === $uuid,
            'Legacy credentials and device bindings must remain intact.');
    }
    $mixed = $service->export([$first['id'], $legacyCodes['pending']['id'], $legacyCodes['active']['id']], 1);
    $assert(array_column($mixed, 'sn') === [$first['sn'], $legacyCodes['pending']['sn'], $legacyCodes['active']['sn']],
        'Mixed export must preserve complete new sixteen-character and legacy thirty-two-character codes.');
    $assert($service->reveal($first['id'], 1)['sn'] === $first['sn'], 'Encrypted reveal must round-trip in MySQL.');
    $firstUuid = $service->view($first['id'], false)['device_uuid'];
    $service->update($first['id'], ['enabled' => false], 1);
    try {
        $service->authorizeSession($first['id'], 2);
        $assert(false, 'Disabled SN authorized.');
    } catch (\yii\web\UnauthorizedHttpException $exception) {
        $assert(true, 'Disabled SN rejected.');
    }
    [$replacement] = $service->generate(2, 1, 'disabled UUID reservation', 1);
    try {
        $service->authenticate($replacement['sn'], $firstUuid, true);
        $assert(false, 'A disabled SN released its UUID to another SN.');
    } catch (\yii\web\ConflictHttpException $exception) {
        $assert(true, 'A disabled SN keeps its UUID reserved.');
    }
    $assert($service->view($first['id'], false)['device_uuid'] === $firstUuid, 'Disabling must retain the original UUID.');
    $assert($service->view($replacement['id'], false)['device_uuid'] === null, 'Rejected replacement must remain unbound.');
    $service->update($first['id'], ['enabled' => true], 1);
    $assert((int)$service->authorizeSession($first['id'], 2)->id === 2, 'Restore keeps original authorization.');

    $redisPort = filter_var(getenv('DEVICE_SN_REDIS_TEST_PORT'), FILTER_VALIDATE_INT);
    if ($redisPort) {
        putenv('AUTH_PROVIDER=legacy');
        putenv('IDENTITY_LOGIN_AUDIT_ENABLED=false');
        Yii::$app->set('redis', new \yii\redis\Connection(['hostname' => '127.0.0.1', 'port' => $redisPort, 'database' => 15]));
        $identity = new \api\modules\v1\services\IdentityService();
        $uuid = $service->view($first['id'], false)['device_uuid'];
        $token = $identity->loginDeviceSn($first['sn'], $uuid, false);
        $assert(isset($token['accessToken'], $token['refreshToken'], $token['expires']), 'Existing token response must be preserved.');
        for ($i = 0; $i < 3; $i++) {
            $claims = Yii::$app->jwt->parse($token['accessToken'])->claims();
            $assert($claims->get('auth_method') === 'device_sn' && (int)$claims->get('device_sn_id') === $first['id'], 'Real refresh must preserve SN claims.');
            $assert($claims->get('exp')->getTimestamp() - $claims->get('iat')->getTimestamp() <= 10800, 'Device JWT lifetime exceeds 3h.');
            $token = $identity->refresh($token['refreshToken']);
        }
        $other = $identity->loginDeviceSn($fourth['sn'], 'idempotent', false);
        $service->update($first['id'], ['enabled' => false], 1);
        try {
            $identity->loginDeviceSn($first['sn'], $uuid, false);
            $assert(false, 'Disabled SN login succeeded.');
        } catch (\yii\web\UnauthorizedHttpException $exception) {
            $assert(true, 'Disabled SN login rejected.');
        }
        try {
            $identity->refresh($token['refreshToken']);
            $assert(false, 'Disabled SN refresh succeeded.');
        } catch (\api\modules\v1\exceptions\DeviceSnAuthenticationException $exception) {
            $assert(true, 'Disabled SN refresh rejected.');
        }
        $assert((int)\api\modules\v1\models\User::findIdentityByAccessToken($token['accessToken'])->id === 2, 'Disabled SN access should retain its grace period.');
        $assert(isset($identity->refresh($other['refreshToken'])['accessToken']), 'Other device refresh was affected.');
        $db->createCommand()->update('user', ['password_hash' => Yii::$app->security->generatePasswordHash('Testing-SN-Only-123!')], ['id' => 2])->execute();
        $passwordToken = $identity->login('player', 'Testing-SN-Only-123!');
        $assert(!Yii::$app->jwt->parse($passwordToken['accessToken'])->claims()->has('device_sn_id'), 'Password login unexpectedly inherited SN provenance.');
        $assert(isset($identity->refresh($passwordToken['refreshToken'])['accessToken']), 'Ordinary password refresh failed.');
        $db->createCommand()->insert('auth_assignment', ['item_name' => 'admin', 'user_id' => '2'])->execute();
        Yii::$app->authManager->invalidateCache();
        try {
            \api\modules\v1\models\User::findIdentityByAccessToken($other['accessToken']);
            $assert(false, 'Elevated account accepted a device access token.');
        } catch (\yii\web\UnauthorizedHttpException $exception) {
            $assert(true, 'Elevated device access rejected.');
        }
    }
    $assert(!in_array('device', $db->schema->getTableNames('', true), true), 'Activation and token flows must not create or depend on the legacy device table.');
    $db->createCommand()->delete('user', ['id' => 2])->execute();
    $assert((int)$db->createCommand('SELECT COUNT(*) FROM device_sn')->queryScalar() === 0, 'User deletion must revoke all account SNs.');
    $assert((int)$db->createCommand('SELECT COUNT(*) FROM audit_log')->queryScalar() > 0, 'User deletion must retain audit events.');
    ob_start();
    $migration->safeDown();
    $migration->safeUp();
    ob_end_clean();
    $assert($db->schema->getTableSchema('device_sn', true) !== null, 'Migration rollback/reapply must work.');
    $assert(!in_array('device', $db->schema->getTableNames('', true), true), 'Migration rollback/reapply must remain independent of the legacy device table.');
    echo 'PASS: real MySQL single-table migration without legacy device, activation races, retry, encrypted distribution, disabled UUID reservation, disable/restore, cascade'
        . ($redisPort ? ', real Redis/JWT login and refresh, password regression' : '') . ' (' . $assertions . " assertions)\n";
} finally {
    $db->close();
    // Children may close inherited PDO sockets; reconnect before owned cleanup.
    $admin = new PDO($baseDsn, 'root', getenv('DEVICE_SN_MYSQL_TEST_PASSWORD') ?: '');
    $admin->exec('DROP DATABASE `' . $database . '`');
}
