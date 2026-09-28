<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use Yii;
use yii\db\Connection;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/** SN authorization uses a single primary connection, never per-statement failover. */
class DeviceSnService
{
    private const UUID_PATTERN = '/^[a-z0-9][a-z0-9._:-]{0,254}$/D';

    private ?Connection $connection;
    private DeviceSnCredential $credential;

    public function __construct(?Connection $connection = null, ?DeviceSnCredential $credential = null)
    {
        $this->connection = $connection;
        $this->credential = $credential ?? new DeviceSnCredential();
    }

    protected function db(): Connection
    {
        return $this->connection ?? Yii::$app->get('deviceSnDb');
    }

    public static function assertEligibleUser(User $user, ?array $roles = null): void
    {
        if ((int)$user->status !== 10 || array_intersect(['root', 'admin', 'manager'], $roles ?? $user->roles)) {
            throw new UnauthorizedHttpException('This account is not eligible for device SN login.');
        }
    }

    public static function normalizeUuid(string $uuid): string
    {
        $uuid = strtolower(trim($uuid));
        // Hardware APIs do not necessarily return an RFC 4122 UUID. Store one
        // canonical ASCII form so casing cannot create duplicate device bindings.
        if (!preg_match(self::UUID_PATTERN, $uuid)) {
            throw new BadRequestHttpException('Invalid device UUID.');
        }
        return $uuid;
    }

    public function generate(int $userId, int $count, string $remark, int $operatorId): array
    {
        if ($count < 1 || $count > 100) {
            throw new BadRequestHttpException('Count must be between 1 and 100.');
        }
        $remark = $this->remark($remark);
        $this->eligibleManagedAccount($userId);
        return $this->db()->transaction(function () use ($userId, $count, $remark, $operatorId) {
            $items = [];
            for ($i = 0; $i < $count; $i++) {
                $credential = $this->credential->generate();
                $now = $this->now();
                $row = $credential;
                unset($row['sn']);
                $row += [
                    'user_id' => $userId, 'device_uuid' => null, 'enabled' => 1,
                    'created_by' => $operatorId, 'created_at' => $now, 'updated_at' => $now,
                    'activated_at' => null, 'last_login_at' => null, 'remark' => $remark,
                ];
                $this->db()->createCommand()->insert('{{%device_sn}}', $row)->execute();
                $id = (int)$this->db()->getLastInsertID();
                $this->audit($id, 'generate', $operatorId);
                $items[] = $this->view($id, false) + ['sn' => $credential['sn']];
            }
            return $items;
        });
    }

    /** Activation is durable independently of downstream token issuance. */
    public function authenticate(string $sn, string $uuid, bool $activate): array
    {
        $hash = DeviceSnCredential::digest($sn);
        $uuid = self::normalizeUuid($uuid);
        try {
            return $this->db()->transaction(function () use ($hash, $uuid, $activate) {
                $row = $this->lockedRow('{{%device_sn}}', 'sn_hash', $hash);
                if (!$row || !(bool)$row['enabled']) {
                    throw new UnauthorizedHttpException('SN credentials are invalid or disabled.');
                }
                $user = $this->eligibleUser((int)$row['user_id']);
                if ($row['activated_at'] !== null || $row['device_uuid'] !== null) {
                    // A partially damaged binding must never become an unbound SN.
                    if ($row['activated_at'] === null || $row['device_uuid'] !== $uuid) {
                        throw new ConflictHttpException('SN is already bound to a different or unavailable device.');
                    }
                } else {
                    if (!$activate) {
                        throw new ConflictHttpException('SN is not activated. Call sn-activate first.');
                    }
                    // The SN row lock prevents one code taking two UUIDs; the unique
                    // device_uuid index prevents two codes taking the same UUID.
                    // Disabled SNs retain their UUID and continue occupying it.
                    $this->db()->createCommand()->update('{{%device_sn}}', [
                        'device_uuid' => $uuid, 'activated_at' => $this->now(), 'updated_at' => $this->now(),
                    ], ['id' => $row['id']])->execute();
                    $this->audit((int)$row['id'], 'activate', (int)$user->id, ['device_uuid' => $uuid]);
                }
                return ['user' => $user, 'device_sn_id' => (int)$row['id']];
            });
        } catch (IntegrityException $exception) {
            throw new ConflictHttpException('SN or device was bound concurrently. Retry with the same credentials.');
        } catch (\yii\db\Exception $exception) {
            if (in_array((int)($exception->errorInfo[1] ?? 0), [1205, 1213], true)) {
                throw new ConflictHttpException('Concurrent device activation. Retry with the same credentials.');
            }
            throw $exception;
        }
    }

    /** Called on every issuance and refresh, including direct refresh-token requests. */
    public function authorizeSession(int $snId, int $userId): User
    {
        $row = (new Query())->select('sn.device_uuid')->from(['sn' => '{{%device_sn}}'])
            ->where(['sn.id' => $snId, 'sn.user_id' => $userId, 'sn.enabled' => 1])
            ->andWhere(['not', ['sn.activated_at' => null]])->one($this->db());
        if (!$row || !is_string($row['device_uuid']) || !preg_match(self::UUID_PATTERN, $row['device_uuid'])) {
            throw new UnauthorizedHttpException('SN authorization is invalid or disabled.');
        }
        return $this->eligibleUser($userId);
    }

    public function recordLogin(int $snId): void
    {
        $this->db()->createCommand()->update('{{%device_sn}}', ['last_login_at' => $this->now()], ['id' => $snId])->execute();
    }

    public function accounts(string $q, int $page, int $pageSize): array
    {
        $query = (new Query())->from(['u' => '{{%user}}'])->where(['u.status' => 10])
            ->andWhere(['not exists', (new Query())->select(new \yii\db\Expression('1'))->from(['a' => '{{%auth_assignment}}'])
                ->where('a.user_id = u.id')->andWhere(['a.item_name' => ['root', 'admin', 'manager']])]);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'u.username', $q], ['like', 'u.nickname', $q]]);
        }
        $total = (int)(clone $query)->count('*', $this->db());
        $items = $query->select(['u.id', 'u.username', 'u.nickname'])->orderBy(['u.id' => SORT_ASC])
            ->offset(($page - 1) * $pageSize)->limit($pageSize)->all($this->db());
        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
        }
        return ['items' => $items, 'total' => $total, 'page' => $page, 'page_size' => $pageSize];
    }

    public function listing(array $filters, int $page, int $pageSize): array
    {
        $query = $this->listingQuery();
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'sn.sn_tail', $q], ['like', 'u.username', $q],
                ['like', 'u.nickname', $q], ['like', 'sn.device_uuid', $q]]);
        }
        if (!empty($filters['user_id'])) {
            $query->andWhere(['sn.user_id' => (int)$filters['user_id']]);
        }
        $status = $filters['status'] ?? '';
        if ($status === 'disabled') {
            $query->andWhere(['sn.enabled' => 0]);
        } elseif ($status === 'pending') {
            $query->andWhere(['sn.enabled' => 1, 'sn.activated_at' => null]);
        } elseif ($status === 'active') {
            $query->andWhere(['sn.enabled' => 1])->andWhere(['not', ['sn.activated_at' => null]]);
        } elseif ($status !== '') {
            throw new BadRequestHttpException('Invalid SN status.');
        }
        $total = (int)(clone $query)->count('*', $this->db());
        $rows = $query->orderBy(['sn.id' => SORT_DESC])->offset(($page - 1) * $pageSize)->limit($pageSize)->all($this->db());
        return ['items' => array_map([$this, 'serialize'], $rows), 'total' => $total, 'page' => $page, 'page_size' => $pageSize];
    }

    public function view(int $id, bool $withEvents = true): array
    {
        $row = $this->listingQuery()->where(['sn.id' => $id])->one($this->db());
        if (!$row) {
            throw new NotFoundHttpException('SN not found.');
        }
        $result = $this->serialize($row);
        if ($withEvents) {
            $events = (new Query())->select(['id', 'event_type', 'action', 'user_id', 'created_at', 'context'])
                ->from('{{%audit_log}}')->where(['resource' => 'device_sn/' . $id, 'event_type' => 'device_sn'])
                ->orderBy(['id' => SORT_DESC])->limit(100)->all($this->db());
            foreach ($events as &$event) {
                $event['context'] = is_string($event['context']) ? json_decode($event['context'], true) : $event['context'];
            }
            $result['events'] = $events;
        }
        return $result;
    }

    public function update(int $id, array $changes, int $operatorId): array
    {
        if (!$changes || array_diff(array_keys($changes), ['enabled', 'remark'])) {
            throw new BadRequestHttpException('Only enabled and remark may be changed.');
        }
        if (array_key_exists('enabled', $changes) && !is_bool($changes['enabled'])) {
            throw new BadRequestHttpException('Enabled must be a boolean.');
        }
        if (array_key_exists('remark', $changes)) {
            if (!is_string($changes['remark'])) {
                throw new BadRequestHttpException('Remark must be a string.');
            }
            $changes['remark'] = $this->remark($changes['remark']);
        }
        return $this->db()->transaction(function () use ($id, $changes, $operatorId) {
            $row = $this->lockedRow('{{%device_sn}}', 'id', $id);
            if (!$row) {
                throw new NotFoundHttpException('SN not found.');
            }
            if (($changes['enabled'] ?? false) === true) {
                $this->eligibleManagedAccount((int)$row['user_id']);
            }
            $values = $changes + ['updated_at' => $this->now()];
            $this->db()->createCommand()->update('{{%device_sn}}', $values, ['id' => $id])->execute();
            $action = array_key_exists('enabled', $changes) ? ($changes['enabled'] ? 'enable' : 'disable') : 'update';
            $this->audit($id, $action, $operatorId, ['fields' => array_keys($changes)]);
            return $this->view($id, false);
        });
    }

    public function reveal(int $id, int $operatorId): array
    {
        $items = $this->export([$id], $operatorId, 'reveal');
        return ['id' => $id, 'sn' => $items[0]['sn']];
    }

    public function export(array $ids, int $operatorId, string $action = 'export'): array
    {
        if (!$ids || count($ids) > 100) {
            throw new BadRequestHttpException('Select between 1 and 100 SN records.');
        }
        foreach ($ids as $id) {
            if (!is_int($id) || $id <= 0) {
                throw new BadRequestHttpException('SN IDs must be positive integers.');
            }
        }
        $ids = array_values(array_unique($ids));
        return $this->db()->transaction(function () use ($ids, $operatorId, $action) {
            $result = [];
            foreach ($ids as $id) {
                $row = $this->lockedRow('{{%device_sn}}', 'id', $id);
                if (!$row) {
                    throw new NotFoundHttpException('SN not found.');
                }
                $sn = $this->credential->decrypt($row['sn_ciphertext'], $row['key_id'], $row['sn_hash']);
                $this->audit($id, $action, $operatorId);
                $result[] = $this->view($id, false) + ['sn' => $sn];
            }
            return $result;
        });
    }

    protected function eligibleUser(int $id): User
    {
        $user = User::find()->where(['id' => $id])->one($this->db());
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('SN account is unavailable.');
        }
        $roles = (new Query())->select('item_name')->from('{{%auth_assignment}}')
            ->where(['user_id' => (string)$id])->column($this->db());
        self::assertEligibleUser($user, $roles);
        return $user;
    }

    private function eligibleManagedAccount(int $id): void
    {
        try {
            $this->eligibleUser($id);
        } catch (UnauthorizedHttpException $exception) {
            // A stale account selection is not an expired root session. Returning
            // 401 here would trigger the iframe's token-refresh/logout machinery.
            throw new BadRequestHttpException('Selected account is unavailable or not an eligible ordinary account.');
        }
    }

    private function listingQuery(): Query
    {
        return (new Query())->select(['sn.id', 'sn.user_id', 'sn.sn_tail', 'sn.enabled', 'sn.remark',
            'sn.created_by', 'sn.created_at', 'sn.updated_at', 'sn.activated_at', 'sn.last_login_at',
            'u.username', 'u.nickname', 'sn.device_uuid'])
            ->from(['sn' => '{{%device_sn}}'])->leftJoin(['u' => '{{%user}}'], 'u.id = sn.user_id');
    }

    private function serialize(array $row): array
    {
        $row['id'] = (int)$row['id'];
        $row['user_id'] = (int)$row['user_id'];
        $row['enabled'] = (bool)$row['enabled'];
        $row['status'] = !$row['enabled'] ? 'disabled' : ($row['activated_at'] !== null ? 'active' : 'pending');
        return $row;
    }

    private function lockedRow(string $table, string $column, $value)
    {
        $sql = 'SELECT * FROM ' . $table . ' WHERE ' . $this->db()->quoteColumnName($column) . ' = :value';
        if ($this->db()->driverName === 'mysql') {
            $sql .= ' FOR UPDATE';
        }
        return $this->db()->createCommand($sql, [':value' => $value])->queryOne();
    }

    private function audit(int $id, string $action, int $operatorId, array $context = []): void
    {
        $this->db()->createCommand()->insert('{{%audit_log}}', [
            'event_type' => 'device_sn', 'user_id' => $operatorId,
            'ip_address' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null,
            'action' => $action, 'resource' => 'device_sn/' . $id,
            'context' => json_encode($context, JSON_THROW_ON_ERROR), 'created_at' => $this->now(),
        ])->execute();
    }

    private function remark(string $remark): string
    {
        $remark = trim($remark);
        if (mb_strlen($remark) > 500) {
            throw new BadRequestHttpException('Remark must be at most 500 characters.');
        }
        return $remark;
    }

    private function now(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
