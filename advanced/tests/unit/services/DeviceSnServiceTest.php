<?php

namespace tests\unit\services;

use api\modules\v1\controllers\PluginSnController;
use api\modules\v1\models\User;
use api\modules\v1\services\DeviceSnCredential;
use api\modules\v1\services\DeviceSnService;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\web\HttpException;
use yii\web\Response;

final class DeviceSnServiceTest extends TestCase
{
    private array $original = [];
    private Connection $db;
    private DeviceSnService $service;
    private $originalConfigs;

    protected function setUp(): void
    {
        $configs = new \ReflectionProperty(\mdm\admin\components\Configs::class, '_instance');
        $this->originalConfigs = $configs->getValue();
        $configs->setValue(null, null);
        foreach (['db', 'deviceSnDb', 'authManager', 'user', 'response', 'request'] as $id) {
            $this->original[$id] = Yii::$app->get($id, false);
        }
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $this->db);
        Yii::$app->set('deviceSnDb', $this->db);
        Yii::$app->set('authManager', new DbManager(['db' => $this->db]));
        Yii::$app->set('response', new Response());
        Yii::$app->set('user', new \yii\web\User(['identityClass' => User::class, 'enableSession' => false]));
        $sql = [
            'CREATE TABLE user (id INTEGER PRIMARY KEY, username TEXT, nickname TEXT, status INTEGER)',
            'CREATE TABLE auth_item (name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER)',
            'CREATE TABLE auth_assignment (item_name TEXT, user_id TEXT, created_at INTEGER)',
            'CREATE TABLE auth_item_child (parent TEXT, child TEXT)',
            'CREATE TABLE device_sn (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, device_uuid TEXT UNIQUE, sn_hash TEXT UNIQUE, sn_ciphertext TEXT, key_id TEXT, sn_tail TEXT, enabled INTEGER, created_by INTEGER, created_at TEXT, updated_at TEXT, activated_at TEXT, last_login_at TEXT, remark TEXT)',
            'CREATE TABLE audit_log (id INTEGER PRIMARY KEY AUTOINCREMENT, event_type TEXT, user_id INTEGER, ip_address TEXT, action TEXT, resource TEXT, context TEXT, created_at TEXT)',
        ];
        foreach ($sql as $statement) {
            $this->db->createCommand($statement)->execute();
        }
        $this->db->createCommand()->batchInsert('user', ['id', 'username', 'nickname', 'status'], [
            [1, 'root', 'Root', 10], [2, 'player', 'Player', 10], [3, 'other', 'Other', 10], [4, 'blocked', 'Blocked', 0],
        ])->execute();
        foreach (['root', 'admin', 'manager', 'user'] as $role) {
            $this->db->createCommand()->insert('auth_item', ['name' => $role, 'type' => 1])->execute();
        }
        $this->db->createCommand()->batchInsert('auth_assignment', ['item_name', 'user_id'], [['root', '1'], ['user', '2'], ['user', '3'], ['user', '4']])->execute();
        $this->service = new DeviceSnService($this->db, new DeviceSnCredential(['test' => base64_encode(random_bytes(32))], 'test'));
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\mdm\admin\components\Configs::class, '_instance'))->setValue(null, $this->originalConfigs);
        foreach ($this->original as $id => $component) {
            Yii::$app->set($id, $component);
        }
        $this->db->close();
    }

    public function testMultipleDevicesForOneAccountAndIdempotentActivation(): void
    {
        [$a, $b] = $this->service->generate(2, 2, 'fleet', 1);
        self::assertSame(19, strlen($a['sn']));
        self::assertSame(19, strlen($b['sn']));
        self::assertNull($a['device_uuid']);
        self::assertNull($b['device_uuid']);
        $first = $this->service->authenticate($a['sn'], 'ROKID-ONE', true);
        $retry = $this->service->authenticate(strtolower($a['sn']), 'rokid-one', true);
        self::assertSame($first['device_sn_id'], $retry['device_sn_id']);
        self::assertSame(2, (int)$this->service->authenticate($b['sn'], 'rokid-two', true)['user']->id);
        self::assertSame(2, (int)(new Query())->from('device_sn')->where(['not', ['device_uuid' => null]])->count('*', $this->db));
        self::assertNull($this->db->schema->getTableSchema('device'));
        self::assertSame(2, (int)$this->service->authorizeSession($a['id'], 2)->id);
        self::assertSame('active', $this->service->view($a['id'])['status']);
        self::assertSame('rokid-one', $this->service->view($a['id'])['device_uuid']);
        self::assertCount(2, $this->service->view($a['id'])['events']);
    }

    public function testExisting32CharacterBindingCoexistsWithNew16CharacterCredentials(): void
    {
        $fixture = require dirname(__DIR__, 2) . '/fixtures/device-sn-legacy.php';
        $legacy = $fixture['row'];
        $stored = $legacy;
        unset($stored['sn']);
        $stored += ['user_id' => 2, 'device_uuid' => 'legacy-rokid', 'enabled' => 1, 'created_by' => 1,
            'created_at' => '2026-09-26 10:00:00', 'updated_at' => '2026-09-26 10:00:00',
            'activated_at' => '2026-09-26 10:00:00', 'last_login_at' => null, 'remark' => 'historical binding'];
        $this->db->createCommand()->insert('device_sn', $stored)->execute();
        $legacyId = (int)$this->db->getLastInsertID();
        $this->service = new DeviceSnService($this->db, new DeviceSnCredential([
            'legacy' => $fixture['key'], 'new' => base64_encode(random_bytes(32)),
        ], 'new'));
        $short = $this->service->generate(2, 1, 'new credential', 1)[0];
        self::assertSame(19, strlen($short['sn']));
        foreach ([false, true] as $activate) {
            $authenticated = $this->service->authenticate(strtolower($legacy['sn']), 'LEGACY-ROKID', $activate);
            self::assertSame($legacyId, $authenticated['device_sn_id']);
            self::assertSame(2, (int)$authenticated['user']->id);
        }
        self::assertSame(2, (int)$this->service->authorizeSession($legacyId, 2)->id);
        self::assertSame($legacy['sn'], $this->service->reveal($legacyId, 1)['sn']);
        self::assertSame([$legacy['sn'], $short['sn']], array_column($this->service->export([$legacyId, $short['id']], 1), 'sn'));
        $this->assertHttp(409, fn() => $this->service->authenticate($short['sn'], 'legacy-rokid', true));
        self::assertSame(2, (int)$this->service->authenticate($short['sn'], 'new-rokid', true)['user']->id);
        $this->assertHttp(409, fn() => $this->service->authenticate($legacy['sn'], 'new-rokid', true));
        $current = (new Query())->from('device_sn')->where(['id' => $legacyId])->one($this->db);
        foreach (['sn_hash', 'sn_ciphertext', 'key_id', 'device_uuid', 'activated_at'] as $field) {
            self::assertSame($stored[$field], $current[$field]);
        }
        $this->service->update($legacyId, ['enabled' => false], 1);
        $this->assertHttp(401, fn() => $this->service->authenticate($legacy['sn'], 'legacy-rokid', false));
        self::assertSame(2, (int)$this->service->authorizeSession($short['id'], 2)->id);
        $this->service->update($legacyId, ['enabled' => true], 1);
        self::assertSame($legacyId, $this->service->authenticate($legacy['sn'], 'legacy-rokid', false)['device_sn_id']);
    }

    public function testUnactivatedCodeDoesNotImplicitlyBindOnLogin(): void
    {
        $sn = $this->service->generate(2, 1, '', 1)[0];
        $this->assertHttp(409, fn() => $this->service->authenticate($sn['sn'], 'rokid-one', false));
        self::assertNull($this->service->view($sn['id'])['device_uuid']);
        self::assertNull($this->service->view($sn['id'])['activated_at']);
    }

    public function testConflictingBindingsCannotBeOverwritten(): void
    {
        [$a, $b] = $this->service->generate(2, 2, '', 1);
        $this->service->authenticate($a['sn'], 'rokid-one', true);
        $this->assertHttp(409, fn() => $this->service->authenticate($a['sn'], 'rokid-two', true));
        $this->assertHttp(409, fn() => $this->service->authenticate($b['sn'], 'rokid-one', true));
        $foreign = $this->service->generate(3, 1, '', 1)[0];
        $this->service->authenticate($foreign['sn'], 'foreign', true);
        $this->assertHttp(409, fn() => $this->service->authenticate($b['sn'], 'foreign', true));
        self::assertSame('pending', $this->service->view($b['id'])['status']);
        self::assertNull($this->service->view($b['id'])['device_uuid']);
        self::assertSame('rokid-one', $this->service->view($a['id'])['device_uuid']);
    }

    public function testDisableAndRestoreAffectOnlyOriginalAuthorization(): void
    {
        [$a, $b] = $this->service->generate(2, 2, '', 1);
        $this->service->authenticate($a['sn'], 'one', true);
        $this->service->authenticate($b['sn'], 'two', true);
        $this->service->update($a['id'], ['enabled' => false], 1);
        $this->assertHttp(401, fn() => $this->service->authenticate($a['sn'], 'one', false));
        $this->assertHttp(401, fn() => $this->service->authorizeSession($a['id'], 2));
        self::assertSame('one', $this->service->view($a['id'])['device_uuid']);
        $other = $this->service->generate(3, 1, '', 1)[0];
        $this->assertHttp(409, fn() => $this->service->authenticate($other['sn'], 'ONE', true));
        self::assertSame(2, (int)$this->service->authorizeSession($b['id'], 2)->id);
        $this->service->update($a['id'], ['enabled' => true], 1);
        self::assertSame(2, (int)$this->service->authenticate($a['sn'], 'one', false)['user']->id);
        $this->assertHttp(409, fn() => $this->service->authenticate($a['sn'], 'new-device', true));
    }

    public function testAccountStateAndRoleAreRechecked(): void
    {
        $this->assertHttp(400, fn() => $this->service->generate(1, 1, '', 1));
        $this->assertHttp(400, fn() => $this->service->generate(4, 1, '', 1));
        $sn = $this->service->generate(2, 1, '', 1)[0];
        $this->service->authenticate($sn['sn'], 'one', true);
        $this->db->createCommand()->insert('auth_assignment', ['item_name' => 'admin', 'user_id' => '2'])->execute();
        Yii::$app->authManager->invalidateCache();
        $this->assertHttp(401, fn() => $this->service->authorizeSession($sn['id'], 2));
        $this->assertHttp(401, fn() => $this->service->authenticate($sn['sn'], 'one', false));
    }

    public function testRevealAndExportAuditWithoutLeakingIntoListOrStorage(): void
    {
        $sn = $this->service->generate(2, 1, '', 1)[0];
        self::assertSame($sn['sn'], $this->service->reveal($sn['id'], 1)['sn']);
        self::assertCount(1, $this->service->export([$sn['id'], $sn['id']], 1));
        $list = $this->service->listing([], 1, 20);
        self::assertArrayNotHasKey('sn', $list['items'][0]);
        self::assertArrayNotHasKey('sn_ciphertext', $list['items'][0]);
        $stored = json_encode((new Query())->from('device_sn')->all($this->db));
        $events = json_encode($this->service->view($sn['id'])['events']);
        self::assertStringNotContainsString(DeviceSnCredential::normalize($sn['sn']), $stored);
        self::assertStringNotContainsString($sn['sn'], $events);
        self::assertCount(3, $this->service->view($sn['id'])['events']);
    }

    public function testMutationAndBatchValidation(): void
    {
        $sn = $this->service->generate(2, 1, '', 1)[0];
        foreach ([['user_id' => 3], ['device_uuid' => null], ['device_uuid' => 'replacement'], ['device_id' => null], ['enabled' => 'false'], ['remark' => []]] as $change) {
            $this->assertHttp(400, fn() => $this->service->update($sn['id'], $change, 1));
        }
        $this->assertHttp(400, fn() => $this->service->generate(2, 101, '', 1));
        $this->assertHttp(400, fn() => $this->service->generate(2, 0, '', 1));
        $this->assertHttp(400, fn() => $this->service->generate(2, 1, str_repeat('字', 501), 1));
        $this->assertHttp(400, fn() => $this->service->export(['1'], 1));
    }

    public function testIncompleteOrInvalidBindingCannotBeReactivatedOrRefreshed(): void
    {
        $sn = $this->service->generate(2, 1, '', 1)[0];
        $this->service->authenticate($sn['sn'], 'one', true);
        foreach ([null, '', 'ONE', 'bad uuid', "one\n", '设备', str_repeat('a', 256)] as $uuid) {
            $this->db->createCommand()->update('device_sn', ['device_uuid' => $uuid], ['id' => $sn['id']])->execute();
            $this->assertHttp(409, fn() => $this->service->authenticate($sn['sn'], 'one', true));
            $this->assertHttp(401, fn() => $this->service->authorizeSession($sn['id'], 2));
            self::assertSame($uuid, $this->service->view($sn['id'])['device_uuid']);
        }
        $this->db->createCommand()->update('device_sn', ['device_uuid' => 'one', 'activated_at' => null], ['id' => $sn['id']])->execute();
        $this->assertHttp(409, fn() => $this->service->authenticate($sn['sn'], 'one', true));
        $this->assertHttp(401, fn() => $this->service->authorizeSession($sn['id'], 2));
    }

    public function testLegacyDeviceRecordsDoNotControlOrChangeSnBinding(): void
    {
        $this->db->createCommand('CREATE TABLE device (id INTEGER PRIMARY KEY, uuid TEXT, owner_id INTEGER, active INTEGER, setup TEXT)')->execute();
        $legacy = ['id' => 1, 'uuid' => 'rokid-one', 'owner_id' => 3, 'active' => 0, 'setup' => '{"legacy":true}'];
        $this->db->createCommand()->insert('device', $legacy)->execute();
        $before = (new Query())->from('device')->all($this->db);
        $sn = $this->service->generate(2, 1, '', 1)[0];
        self::assertSame(2, (int)$this->service->authenticate($sn['sn'], 'rokid-one', true)['user']->id);
        self::assertSame(2, (int)$this->service->authorizeSession($sn['id'], 2)->id);
        self::assertSame($before, (new Query())->from('device')->all($this->db));
        $this->db->createCommand()->dropTable('device')->execute();
        self::assertSame(2, (int)$this->service->authenticate($sn['sn'], 'rokid-one', false)['user']->id);
    }

    public function testActivationAuditFailureRollsBackUuidAndAllowsSamePairRetry(): void
    {
        $sn = $this->service->generate(2, 1, '', 1)[0];
        $this->db->createCommand()->renameTable('audit_log', 'saved_audit_log')->execute();
        try {
            $this->service->authenticate($sn['sn'], 'one', true);
            self::fail('Missing audit storage must roll back activation.');
        } catch (\yii\db\Exception $exception) {
            $row = $this->service->view($sn['id'], false);
            self::assertNull($row['device_uuid']);
            self::assertNull($row['activated_at']);
        }
        $this->db->createCommand()->renameTable('saved_audit_log', 'audit_log')->execute();
        self::assertSame(2, (int)$this->service->authenticate($sn['sn'], 'one', true)['user']->id);
    }

    public function testGenerationRollsBackWhenAuditFails(): void
    {
        $this->db->createCommand()->dropTable('audit_log')->execute();
        try {
            $this->service->generate(2, 3, '', 1);
            self::fail('Missing audit storage must fail the operation.');
        } catch (\yii\db\Exception $exception) {
            self::assertSame(0, (int)(new Query())->from('device_sn')->count('*', $this->db));
        }
    }

    public function testEligibleAccountsAndMaskedSearch(): void
    {
        self::assertSame([2, 3], array_column($this->service->accounts('', 1, 20)['items'], 'id'));
        $items = $this->service->generate(2, 3, '', 1);
        self::assertSame(3, $this->service->listing(['q' => 'player', 'status' => 'pending'], 1, 2)['total']);
        self::assertCount(1, $this->service->listing([], 2, 2)['items']);
        $this->service->authenticate($items[0]['sn'], 'rokid-search', true);
        $matched = $this->service->listing(['q' => 'rokid-search', 'status' => 'active'], 1, 20);
        self::assertSame(1, $matched['total']);
        self::assertSame('rokid-search', $matched['items'][0]['device_uuid']);
    }

    public function testEveryManagementActionRequiresRootEvenWhenCalledDirectly(): void
    {
        $controller = new PluginSnController('plugin-sn', Yii::$app);
        $calls = [fn() => $controller->actionIndex(), fn() => $controller->actionAccounts(),
            fn() => $controller->actionView(1), fn() => $controller->actionGenerate(),
            fn() => $controller->actionUpdate(1), fn() => $controller->actionReveal(1), fn() => $controller->actionExport()];
        foreach ($calls as $call) {
            $this->assertHttp(401, $call);
        }
        Yii::$app->user->setIdentity(User::findOne(2));
        foreach ($calls as $call) {
            $this->assertHttp(403, $call);
        }
        Yii::$app->user->setIdentity(User::findOne(1));
        self::assertSame(0, $controller->actionIndex()['data']['total']);
        self::assertSame('no-store', Yii::$app->response->headers->get('Cache-Control'));
    }

    private function assertHttp(int $status, callable $action): void
    {
        try {
            $action();
            self::fail('Expected HTTP ' . $status);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->statusCode, $exception->getMessage());
        }
    }
}
