<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityUserProjection;
use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\models\User;
use PHPUnit\Framework\TestCase;
use yii\db\Connection;

/** SQLite checks data invariants; these tests do not exercise MySQL locking. */
class IdentityUserProjectionTest extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite is required for isolated projection checks.');
        }
        $this->db = new ProjectionSqliteConnection(['dsn' => 'sqlite::memory:']);
        $this->db->createCommand('CREATE TABLE user (id INTEGER PRIMARY KEY, username TEXT UNIQUE, nickname TEXT, email TEXT UNIQUE,
            status INTEGER, email_verified_at INTEGER, auth_key TEXT, password_hash TEXT, password_reset_token TEXT,
            created_at INTEGER, updated_at INTEGER)')->execute();
        $this->db->createCommand('CREATE TABLE user_info (user_id INTEGER, gold INTEGER DEFAULT 0, points INTEGER DEFAULT 0, avatar_id INTEGER)')->execute();
        $this->db->createCommand('CREATE TABLE identity_user_projection_state (user_id INTEGER PRIMARY KEY, version INTEGER)')->execute();
        $this->db->createCommand('CREATE TABLE identity_legacy_wechat_scenes (scene_hash CHAR(64) PRIMARY KEY,
            provider_app_id VARCHAR(128), issued_at INTEGER, expires_at INTEGER, source VARCHAR(64))')->execute();
        $this->db->createCommand()->insert('user', ['id' => 42, 'username' => 'original', 'email' => 'original@example.test',
            'status' => 10, 'auth_key' => 'legacy-key', 'password_hash' => 'legacy-hash', 'created_at' => 1])->execute();
        $this->db->createCommand()->insert('user_info', ['user_id' => 42, 'gold' => 50, 'points' => 8, 'avatar_id' => 88])->execute();
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }
    }

    public function testProjectionPreservesBusinessDataAndIgnoresCredentialPayloads(): void
    {
        $projection = new IdentityUserProjection();
        $event = $this->event(42, 3, 'current', 'current@example.test');
        $event['subject']['password_hash'] = 'never-copy-this';
        $event['subject']['gold'] = 999;
        $this->assertTrue($projection->apply($this->db, $event));
        $user = $this->db->createCommand('SELECT * FROM user WHERE id=42')->queryOne();
        $info = $this->db->createCommand('SELECT * FROM user_info WHERE user_id=42')->queryOne();
        $this->assertSame('current', $user['username']);
        $this->assertSame('!identity-owned', $user['password_hash']);
        $this->assertSame('', $user['auth_key']);
        $this->assertSame(1, (int)$user['created_at']);
        $this->assertSame(50, (int)$info['gold']);
        $this->assertSame(8, (int)$info['points']);
        $this->assertSame(88, (int)$info['avatar_id']);
        $this->assertFalse($projection->apply($this->db, $event));
        $this->assertFalse($projection->apply($this->db, $this->event(42, 2, 'older', null)));
        $this->assertSame('current', $this->db->createCommand('SELECT username FROM user WHERE id=42')->queryScalar());
    }

    public function testUniqueEmailCollisionCannotOverwriteAnotherBusinessOwner(): void
    {
        try {
            (new IdentityUserProjection())->apply($this->db, $this->event(43, 4, 'new-owner', 'original@example.test'));
            $this->fail('A projection must fail closed when a unique field belongs to another stable user ID.');
        } catch (\yii\db\IntegrityException $exception) {
            $this->assertSame('original', $this->db->createCommand('SELECT username FROM user WHERE id=42')->queryScalar());
            $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM user')->queryScalar());
            $this->assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM identity_user_projection_state WHERE user_id=43')->queryScalar());
        }
    }

    public function testLegacyPendingQrCaptureStoresOnlyDigestAndProvenExpiry(): void
    {
        $previous = getenv('IDENTITY_DATA_SOURCE');
        putenv('IDENTITY_DATA_SOURCE=legacy');
        try {
            \api\modules\v1\services\LegacyWechatSceneRecorder::record($this->db, 'client-visible-scene', 'exact-app',
                1700000000, 1700003600, 'api/wechat/qrcode');
            $row = $this->db->createCommand('SELECT * FROM identity_legacy_wechat_scenes')->queryOne();
            $this->assertSame(hash('sha256', 'client-visible-scene'), $row['scene_hash']);
            $this->assertSame(1700003600, (int)$row['expires_at']);
            $this->assertSame('exact-app', $row['provider_app_id']);
            $this->assertArrayNotHasKey('open_id', $row);
            $this->assertArrayNotHasKey('token', $row);
        } finally {
            putenv($previous === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $previous);
        }
    }

    public function testMissingBusinessTargetIsMaterializedFromIdentityWithoutCredentials(): void
    {
        $previousDb = \Yii::$app->db;
        $previousAuthority = \Yii::$app->has('identityAuthority') ? \Yii::$app->get('identityAuthority') : null;
        $previousMode = getenv('IDENTITY_DATA_SOURCE');
        \Yii::$app->set('db', $this->db);
        \Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => new ProjectionSubjectClient()]));
        putenv('IDENTITY_DATA_SOURCE=identity');
        try {
            $user = User::findOne(73);
            $this->assertSame(73, (int)$user->id);
            $this->assertSame('identity-user', $user->username);
            $this->assertSame('!identity-owned', $user->password_hash);
            $this->assertSame('', $user->auth_key);
            $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=73')->queryScalar());
        } finally {
            \Yii::$app->set('db', $previousDb);
            \Yii::$app->set('identityAuthority', $previousAuthority);
            putenv($previousMode === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $previousMode);
        }
    }

    public function testFirstRequestProjectionCollisionRollsBackTheBusinessProfile(): void
    {
        $previousDb = \Yii::$app->db;
        \Yii::$app->set('db', $this->db);
        try {
            try {
                (new IdentityAuthorityService())->projectSubject(['id' => 43, 'username' => 'new-owner',
                    'email' => 'original@example.test', 'status' => 10, 'roles' => ['user']]);
                $this->fail('A secondary unique key cannot create an orphan target profile.');
            } catch (\common\components\security\ServiceUnavailableHttpException $exception) {
                $this->assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=43')->queryScalar());
                $this->assertSame('original', $this->db->createCommand('SELECT username FROM user WHERE id=42')->queryScalar());
            }
        } finally {
            \Yii::$app->set('db', $previousDb);
        }
    }

    private function event(int $id, int $version, string $username, ?string $email): array
    {
        return ['userId' => $id, 'version' => $version, 'subject' => ['id' => $id, 'username' => $username, 'email' => $email, 'status' => 10]];
    }
}

class ProjectionSqliteConnection extends Connection
{
    public function createCommand($sql = null, $params = [])
    {
        // SQLite has a single writer; FOR UPDATE is a MySQL locking concern.
        return parent::createCommand($sql === null ? null : str_replace(' FOR UPDATE', '', $sql), $params);
    }
}

class ProjectionSubjectClient extends IdentityProviderClient
{
    public function identitySubject(int $id): array
    {
        return ['id' => $id, 'username' => 'identity-user', 'status' => 10, 'roles' => ['user']];
    }
}
