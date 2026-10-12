<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use Yii;
use yii\base\Component;
use yii\web\UnauthorizedHttpException;
use common\components\security\ServiceUnavailableHttpException;
use api\modules\v1\components\DeviceSnAuthContext;

/** Request-scoped authoritative reads; no cross-request permission cache. */
class IdentityAuthorityService extends Component
{
    public ?IdentityProviderClient $client = null;
    private array $subjects = [];
    private array $subjectSummaries = [];
    private array $organizationMembers = [];

    public function invalidate(): void
    {
        $this->subjects = $this->subjectSummaries = $this->organizationMembers = [];
    }

    public function subject(int $id): array
    {
        if (!isset($this->subjects[$id])) {
            $this->subjects[$id] = $this->validateSubject($this->provider()->identitySubject($id), $id);
        }

        return $this->subjects[$id];
    }

    public function verifyAccessToken(string $token): User
    {
        $subject = $this->validateSubject($this->provider()->verifyIdentityAccessToken($token));
        if ($subject['status'] !== 10) {
            throw new UnauthorizedHttpException('Account is disabled.');
        }
        $this->subjects[$subject['id']] = $subject;

        $user = $this->projectSubject($subject);
        // Only Identity's verified response supplies request credential provenance.
        // The display projection never persists or reconstructs authentication.
        $user->authContext = DeviceSnAuthContext::normalize($subject['authContext'] ?? []);
        return $user;
    }

    public function identityById(int $id): ?User
    {
        $subject = $this->subject($id);
        return $subject['status'] === 10 ? $this->projectSubject($subject) : null;
    }

    public function allowed(int $id, string $permission): bool
    {
        return $this->provider()->identityPermission($id, $permission);
    }

    public function roles(int $id): array
    {
        $subject = $this->subjects[$id] ?? $this->subjectSummaries[$id] ?? $this->subject($id);
        return $subject['status'] === 10 ? $subject['roles'] : [];
    }

    public function organizations(int $id): array
    {
        $subject = $this->subjects[$id] ?? $this->subjectSummaries[$id] ?? $this->subject($id);
        return $subject['status'] === 10 ? $subject['organizations'] : [];
    }

    /** Current membership summaries are not a substitute for full permission reads. */
    public function organizationMembers(int $id): array
    {
        if (!isset($this->organizationMembers[$id])) {
            $data = $this->provider()->identityOrganizationMembers($id);
            if (($data['organization']['id'] ?? null) !== $id || !is_array($data['subjects'] ?? null)) {
                throw new ServiceUnavailableHttpException('Identity returned invalid organization membership.');
            }
            foreach ($data['subjects'] as &$subject) {
                if (!is_array($subject)) {
                    throw new ServiceUnavailableHttpException('Identity returned invalid organization membership.');
                }
                $subject = $this->validateSubject($subject);
                $this->subjectSummaries[$subject['id']] = $subject;
            }
            unset($subject);
            $this->organizationMembers[$id] = $data;
        }
        return $this->organizationMembers[$id];
    }

    /** Only this writer may materialize Identity data in the business database. */
    public function projectSubject(array $subject): User
    {
        $subject = $this->validateSubject($subject);
        $id = (int)$subject['id'];
        $user = User::find()->where(['id' => $id])->one();
        if ($user === null) {
            $db = Yii::$app->has('identityProjectionDb') ? Yii::$app->get('identityProjectionDb') : Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                // Keep the old PK for existing ownership FKs. Credentials are
                // never copied. The one-way placeholder also supports old
                // schemas whose credential columns are still NOT NULL.
                $db->createCommand()->upsert('{{%user}}', [
                    'id' => $id,
                    'username' => $subject['username'],
                    'nickname' => $subject['nickname'],
                    'email' => $subject['email'],
                    'email_verified_at' => $subject['emailVerifiedAt'],
                    'status' => $subject['status'],
                    'auth_key' => '',
                    'password_hash' => '!identity-owned',
                    'created_at' => time(),
                    'updated_at' => time(),
                ], false)->execute();
                // A duplicate username/email may have suppressed insertion
                // without creating this ID. Fail inside the transaction,
                // before an orphan business profile can be inserted.
                if (!(new \yii\db\Query())->from('{{%user}}')->where(['id' => $id])->exists($db)) {
                    throw new ServiceUnavailableHttpException('Business user projection conflicts with another identity.');
                }
                // gold/points/avatar belong to MRPP; do not overwrite them.
                // upsert(user) serializes concurrent first requests on the
                // same PK; user_info historically has no unique user_id key.
                $exists = (new \yii\db\Query())->from('{{%user_info}}')->where(['user_id' => $id])->exists($db);
                if (!$exists) {
                    $db->createCommand()->insert('{{%user_info}}', ['user_id' => $id])->execute();
                }
                $transaction->commit();
            } catch (\Throwable $exception) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                throw new ServiceUnavailableHttpException('Business user projection is unavailable.', 0, $exception);
            }
            $user = User::find()->where(['id' => $id])->one();
            if ($user === null) {
                throw new ServiceUnavailableHttpException('Business user projection was not materialized.');
            }
        }

        // Current request always sees authoritative values, even while a
        // background display projection is catching up.
        $user->username = $subject['username'];
        $user->nickname = $subject['nickname'];
        $user->email = $subject['email'];
        $user->email_verified_at = $subject['emailVerifiedAt'];
        $user->status = $subject['status'];
        $user->identitySubject = $subject;

        return $user;
    }

    protected function provider(): IdentityProviderClient
    {
        return $this->client ??= new IdentityProviderClient();
    }

    private function validateSubject(array $subject, ?int $expectedId = null): array
    {
        $id = $subject['id'] ?? null;
        $roles = $subject['roles'] ?? null;
        $organizations = $subject['organizations'] ?? [];
        $permissions = $subject['permissions'] ?? [];
        $directPermissions = $subject['directPermissions'] ?? [];
        if (!is_int($id) || $id <= 0 || ($expectedId !== null && $expectedId !== $id)
            || !is_string($subject['username'] ?? null) || $subject['username'] === ''
            || !is_array($roles) || array_filter($roles, static fn($role) => !is_string($role)) !== []
            || !is_array($permissions) || array_filter($permissions, static fn($permission) => !is_string($permission)) !== []
            || !is_array($directPermissions) || array_filter($directPermissions, static fn($permission) => !is_string($permission)) !== []
            || !is_array($organizations)
            || !isset($subject['status']) || !is_int($subject['status'])) {
            throw new ServiceUnavailableHttpException('Identity returned an invalid subject.');
        }

        return array_merge([
            'username' => null, 'nickname' => null, 'email' => null,
            'emailVerifiedAt' => null, 'organizations' => [], 'permissions' => [], 'directPermissions' => [],
        ], $subject);
    }
}
