<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityUserProjection;
use PHPUnit\Framework\TestCase;
use yii\db\Connection;

/** Opt-in isolated MySQL tests execute real Yii migrations and projection code. */
class IdentityUserProjectionMysqlTest extends TestCase
{
    private ?Connection $admin = null;
    private ?Connection $db = null;
    private ?string $database = null;

    protected function setUp(): void
    {
        if (getenv('IDENTITY_TEST_MYSQL') !== '1') {
            $this->markTestSkipped('Set IDENTITY_TEST_MYSQL=1 for isolated MySQL integration.');
        }
        if (!in_array('mysql', \PDO::getAvailableDrivers(), true) && !class_exists('ProjectionRpcPdo')) {
            $this->markTestSkipped('PDO MySQL or the explicit WASM MySQL test bridge is required.');
        }
        $this->admin = $this->connection();
        $this->database = 'identity_php_projection_' . bin2hex(random_bytes(8));
        $this->admin->createCommand('CREATE DATABASE `' . $this->database . '` CHARACTER SET utf8mb4')->execute();
        $this->db = $this->connection($this->database);
        $migrations = [
            'm130524_201442_init', 'm190124_110200_add_verification_token_column_to_user_table',
            'm200229_134259_add_access_token_column_to_user_table', 'm211205_210923_add_nickname_column_to_user_table',
            'm260121_000000_add_email_verified_at_to_user_table', 'm260422_000002_allow_null_user_email',
            'm191018_142108_create_file_table', 'm220314_024610_create_user_info_table',
            'm220316_092100_add_avatar_id_column_to_user_info_table', 'm220316_092238_drop_avatar_column_from_user_info_table',
            'm220329_064116_add_gold_column_points_column_to_user_info_table',
            'm211207_232655_create_verse_table', 'm261012_010000_create_identity_business_projection_state',
        ];
        ob_start();
        try {
            foreach ($migrations as $name) {
                require_once dirname(__DIR__, 3) . '/console/migrations/' . $name . '.php';
                $migration = new $name(['db' => $this->db]);
                $migration->up();
            }
        } finally {
            ob_end_clean();
        }
        $this->db->createCommand()->insert('user', ['id' => 42, 'username' => 'original', 'email' => 'owner@example.test',
            'auth_key' => 'old-key', 'password_hash' => 'old-password', 'password_reset_token' => 'old-reset',
            'verification_token' => 'old-verification', 'access_token' => 'old-access', 'status' => 10,
            'created_at' => 1, 'updated_at' => 1])->execute();
        $this->db->createCommand()->insert('file', ['id' => 8, 'md5' => 'avatar', 'url' => 'https://example.test/avatar'])->execute();
        $this->db->createCommand()->insert('user_info', ['user_id' => 42, 'gold' => 50, 'points' => 8, 'avatar_id' => 8])->execute();
        $this->db->createCommand()->insert('verse', ['id' => 7, 'author_id' => 42, 'updater_id' => 42, 'name' => 'Owned content'])->execute();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            $this->db->close();
        }
        if ($this->admin !== null && $this->database !== null) {
            $this->admin->createCommand('DROP DATABASE `' . $this->database . '`')->execute();
            $this->admin->close();
        }
    }

    public function testRealMysqlProjectionRetainsBusinessDataAndIsIdempotent(): void
    {
        $this->retire();
        $projection = new IdentityUserProjection();
        $this->assertTrue($projection->apply($this->db, $this->event(42, 3, 'current', 'current@example.test')));
        $this->assertFalse($projection->apply($this->db, $this->event(42, 3, 'current', 'current@example.test')));
        $this->assertFalse($projection->apply($this->db, $this->event(42, 2, 'stale', null)));
        $info = $this->db->createCommand('SELECT * FROM user_info WHERE user_id=42')->queryOne();
        $this->assertSame(50, (int)$info['gold']);
        $this->assertSame(8, (int)$info['points']);
        $this->assertSame(8, (int)$info['avatar_id']);
        $this->assertSame(42, (int)$this->db->createCommand('SELECT author_id FROM verse WHERE id=7')->queryScalar());
        $this->assertTrue($projection->apply($this->db, $this->event(73, 1, 'new-user', null)));
        $this->assertSame('!identity-owned', $this->db->createCommand('SELECT password_hash FROM user WHERE id=73')->queryScalar());
    }

    public function testRealMysqlUniqueCollisionCannotChangeAnExistingOwner(): void
    {
        $this->retire();
        try {
            (new IdentityUserProjection())->apply($this->db, $this->event(43, 1, 'collision', 'owner@example.test'));
            $this->fail('Unique email must reject a different canonical user ID.');
        } catch (\yii\db\IntegrityException $exception) {
            $this->assertSame('original', $this->db->createCommand('SELECT username FROM user WHERE id=42')->queryScalar());
            $this->assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM identity_user_projection_state WHERE user_id=43')->queryScalar());
            $this->assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=43')->queryScalar());
        }
    }

    public function testCredentialConstraintsRejectLegacyWritesAndAllowProfileProjection(): void
    {
        $this->retire();
        foreach (['password_hash' => 'new-local-password', 'auth_key' => 'new-key', 'password_reset_token' => 'reset',
            'verification_token' => 'verify', 'access_token' => 'local-access'] as $column => $value) {
            try {
                $this->db->createCommand()->update('user', [$column => $value], ['id' => 42])->execute();
                $this->fail('Local credential mutation must be rejected: ' . $column);
            } catch (\yii\db\Exception $exception) {
                $this->assertStringContainsString('chk_identity_business_credentials', $exception->getMessage());
            }
        }
        $this->db->createCommand()->update('user', ['nickname' => 'Projected', 'status' => 0], ['id' => 42])->execute();
        $this->assertSame('Projected', $this->db->createCommand('SELECT nickname FROM user WHERE id=42')->queryScalar());
        $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM verse WHERE id=7')->queryScalar());
    }

    public function testDeletionAndIdMutationCannotCascadeBusinessContent(): void
    {
        $cascade = $this->db->createCommand("SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME='fk-verse-author_id'")->queryScalar();
        $this->assertSame('CASCADE', $cascade);
        $this->retire();
        foreach (['DELETE FROM user WHERE id=42', 'UPDATE user SET id=999 WHERE id=42'] as $sql) {
            try {
                $this->db->createCommand($sql)->execute();
                $this->fail('Stable business users cannot be deleted or renumbered.');
            } catch (\yii\db\Exception $exception) {
                $this->assertStringContainsString('Identity business projection', $exception->getMessage());
            }
        }
        $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM verse WHERE id=7')->queryScalar());
        $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=42')->queryScalar());
    }

    public function testFirstRequestMysqlUpsertCannotLeaveAnOrphanProfile(): void
    {
        $this->retire();
        $previousDb = \Yii::$app->db;
        \Yii::$app->set('db', $this->db);
        try {
            try {
                (new IdentityAuthorityService())->projectSubject($this->event(43, 1, 'collision', 'owner@example.test')['subject']);
                $this->fail('Secondary-key upsert must not materialize a different user ID.');
            } catch (\common\components\security\ServiceUnavailableHttpException $exception) {
                $this->assertSame(0, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=43')->queryScalar());
                $this->assertSame('original', $this->db->createCommand('SELECT username FROM user WHERE id=42')->queryScalar());
            }
            $user = (new IdentityAuthorityService())->projectSubject($this->event(73, 1, 'new-user', null)['subject']);
            $this->assertSame(73, (int)$user->id);
            $this->assertSame(1, (int)$this->db->createCommand('SELECT COUNT(*) FROM user_info WHERE user_id=73')->queryScalar());
        } finally {
            \Yii::$app->set('db', $previousDb);
        }
    }

    private function connection(?string $database = null): Connection
    {
        $dsn = 'mysql:host=' . (getenv('IDENTITY_TEST_MYSQL_HOST') ?: '127.0.0.1')
            . ';port=' . (getenv('IDENTITY_TEST_MYSQL_PORT') ?: '13316')
            . ($database === null ? '' : ';dbname=' . $database);
        $db = new Connection(['dsn' => $dsn, 'username' => getenv('IDENTITY_TEST_MYSQL_USER') ?: 'identity_test',
            'password' => getenv('IDENTITY_TEST_MYSQL_PASSWORD') ?: '', 'charset' => 'utf8mb4']);
        if (class_exists('ProjectionRpcPdo')) {
            $db->pdo = new \ProjectionRpcPdo($dsn);
        }
        return $db;
    }

    private function retire(): void
    {
        $this->db->createCommand("SET @identity_authority_confirmed='identity'")->execute();
        $source = file_get_contents(dirname(__DIR__, 4) . '/docs/sql/identity-retire-business-credentials.sql');
        $delimiter = ';'; $statement = '';
        foreach (explode("\n", $source) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '--')) {
                continue;
            }
            if (str_starts_with($line, 'DELIMITER ')) {
                $delimiter = trim(substr($line, 10));
                continue;
            }
            $statement .= $line . "\n";
            if (str_ends_with(trim($statement), $delimiter)) {
                $sql = substr(trim($statement), 0, -strlen($delimiter));
                $this->db->createCommand($sql)->execute();
                $statement = '';
            }
        }
    }

    private function event(int $id, int $version, string $username, ?string $email): array
    {
        return ['userId' => $id, 'version' => $version, 'subject' => ['id' => $id, 'username' => $username,
            'email' => $email, 'status' => 10, 'roles' => ['user']]];
    }
}
