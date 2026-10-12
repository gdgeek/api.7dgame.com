<?php

namespace api\modules\v1\services;

use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
/** Reserve the caller's key and commit the new object and receipt in one transaction. */
final class ReliableCreate
{
    private static function actor(): int
    {
        $id = (int) Yii::$app->user->id;
        if ($id < 1) {
            throw new UnauthorizedHttpException('Authentication required');
        }
        return $id;
    }
    private static function operation(string $value): string
    {
        if (!ReliableWrite::validId($value)) {
            throw new BadRequestHttpException('Use a UUID Idempotency-Key');
        }
        return strtolower($value);
    }
    private static function row(string $operation): array|false
    {
        return (new Query())->from('{{%webmcp_operation}}')->where(['actor_id' => self::actor(), 'operation_id' => $operation])->one(Yii::$app->db);
    }
    private static function acknowledge(array $row, string $type, callable $authorize): array
    {
        if ($row['target_type'] !== $type || $row['action'] !== 'create') {
            throw new ConflictHttpException('Operation key belongs to another action');
        }
        $class = ReliableWrite::modelClass($type);
        $model = $class::findOne((int) $row['target_id']);
        if ($model === null) {
            throw new NotFoundHttpException('Created object is no longer available; it will not be recreated');
        }
        $authorize($model);
        $receipt = json_decode($row['receipt'], true, 64, JSON_THROW_ON_ERROR);
        // Return the committed creation identity/revision; do not pretend it is today's editable revision.
        return [
            'id' => (int) $row['target_id'],
            'uuid' => $receipt['uuid'],
            'serverRevision' => $receipt['serverRevision'],
            'writeReceipt' => $receipt,
            'replayed' => true,
        ];
    }
    public static function run(string $type, array $body, callable $authorize): array
    {
        $operation = self::operation((string) Yii::$app->request->headers->get('Idempotency-Key'));
        if (Yii::$app->request->headers->has('If-Match')) {
            throw new BadRequestHttpException('New objects do not accept If-Match');
        }
        $actor = self::actor();
        if (!in_array($type, ['meta', 'verse'], true)) {
            throw new BadRequestHttpException('Invalid creation target');
        }
        if (!is_string($body['uuid'] ?? null) || !ReliableWrite::validId($body['uuid'])) {
            throw new BadRequestHttpException('New objects require a stable UUID');
        }
        if (strlen(ContentRevision::canonical($body)) > 2097152) {
            throw new BadRequestHttpException('Creation data too large');
        }
        $hash = ContentRevision::hash([$type, 'create', $body]);
        $class = ReliableWrite::modelClass($type);
        $db = $class::getDb();
        if ($db->getTransaction()?->isActive) {
            throw new \LogicException('Creation must own its commit boundary');
        }
        return $db->useMaster(fn() => $db->noCache(function () use ($db, $class, $type, $body, $authorize, $operation, $actor, $hash) {
            $previous = self::row($operation);
            if ($previous !== false) {
                if (!hash_equals($previous['request_hash'], $hash)) {
                    throw new ConflictHttpException('Operation ID already used for a different request');
                }
                return self::acknowledge($previous, $type, $authorize);
            }
            $tx = $db->beginTransaction();
            try {
                // This unique-key reservation is invisible until the entire creation commits.
                $db->createCommand()->insert('{{%webmcp_operation}}', [
                    'actor_id' => $actor,
                    'operation_id' => $operation,
                    'target_type' => $type,
                    'target_id' => 0,
                    'action' => 'create',
                    'request_hash' => $hash,
                    'receipt' => '{}',
                    'created_at' => time(),
                ])->execute();
                $model = new $class();
                $model->load($body, '');
                $model->author_id = $actor;
                if ($type === 'meta') {
                    $model->prefab = 0;
                }
                if (!$model->save()) {
                    throw new BadRequestHttpException('Invalid creation data; nothing was saved');
                }
                if (!$model->refresh()) {
                    throw new \RuntimeException('Created object could not be reloaded');
                }
                $authorize($model);
                $receipt = [
                    'operationId' => $operation,
                    'status' => 'completed',
                    'targetType' => $type,
                    'targetId' => (int) $model->id,
                    'action' => 'create',
                    'serverRevision' => ContentRevision::of($model),
                    'uuid' => $model->uuid,
                ];
                $db->createCommand()->update('{{%webmcp_operation}}', ['target_id' => (int) $model->id, 'receipt' => ContentRevision::canonical($receipt)], ['actor_id' => $actor, 'operation_id' => $operation])->execute();
                $tx->commit();
                return [
                    'id' => (int) $model->id,
                    'uuid' => $model->uuid,
                    'serverRevision' => $receipt['serverRevision'],
                    'writeReceipt' => $receipt,
                    'replayed' => false,
                ];
            } catch (\Throwable $error) {
                if ($tx->isActive) {
                    $tx->rollBack();
                }
                if ($error instanceof \yii\db\IntegrityException) {
                    $previous = self::row($operation);
                    if ($previous !== false) {
                        if (!hash_equals($previous['request_hash'], $hash)) {
                            throw new ConflictHttpException('Operation ID already used for a different request');
                        }
                        return self::acknowledge($previous, $type, $authorize);
                    }
                }
                throw $error;
            }
        }));
    }
    public static function receipt(string $type, string $operation, callable $authorize): array
    {
        $operation = self::operation($operation);
        return Yii::$app->db->useMaster(fn() => Yii::$app->db->noCache(function () use ($type, $operation, $authorize) {
            $row = self::row($operation);
            if ($row === false) {
                throw new NotFoundHttpException('Creation not observed; this does not prove failure');
            }
            return self::acknowledge($row, $type, $authorize);
        }));
    }

    /** Read-only recovery. A UUID match is object evidence, never an invented operation receipt. */
    public static function lookup(string $type, mixed $operation, mixed $uuid, callable $authorize): array
    {
        if (!in_array($type, ['meta', 'verse'], true)) {
            throw new BadRequestHttpException('Invalid creation target');
        }
        $actor = self::actor();
        if (($operation !== null && !is_string($operation)) || ($uuid !== null && !is_string($uuid))) {
            throw new BadRequestHttpException('Creation identifiers must be UUID strings');
        }
        $operation = $operation === null ? null : self::operation($operation);
        if ($uuid !== null && !ReliableWrite::validId($uuid)) {
            throw new BadRequestHttpException('creationUuid must be a UUID');
        }
        $uuid = $uuid === null ? null : strtolower($uuid);
        if ($operation === null && $uuid === null) {
            throw new BadRequestHttpException('Provide operationId or creationUuid');
        }
        $base = [
            'contractVersion' => 'creation-recovery-v1', 'targetType' => $type,
            'operationId' => $operation, 'creationUuid' => $uuid,
            'verification' => 'none', 'operationVerified' => false, 'retrySafe' => false,
        ];
        return Yii::$app->db->useMaster(fn() => Yii::$app->db->noCache(function () use ($type, $operation, $uuid, $authorize, $actor, $base) {
            $row = $operation === null ? false : self::row($operation);
            if ($row !== false) {
                if ($row['target_type'] !== $type || $row['action'] !== 'create') {
                    return array_merge($base, ['status' => 'conflict', 'reason' => 'operation_key_conflict']);
                }
                try {
                    $ack = self::acknowledge($row, $type, $authorize);
                } catch (NotFoundHttpException) {
                    return array_merge($base, ['status' => 'indeterminate', 'reason' => 'created_object_unavailable']);
                } catch (\yii\web\ForbiddenHttpException) {
                    return array_merge($base, ['status' => 'indeterminate', 'reason' => 'permission_denied']);
                }
                if ($uuid !== null && strtolower($ack['uuid']) !== $uuid) {
                    return array_merge($base, ['status' => 'conflict', 'reason' => 'creation_uuid_mismatch']);
                }
                return array_merge($base, $ack, [
                    'status' => 'completed', 'reason' => 'creation_receipt',
                    'verification' => 'server_acknowledged', 'operationVerified' => true,
                    'requestHash' => $row['request_hash'], 'recordedAt' => (int) $row['created_at'],
                ]);
            }
            if ($uuid !== null) {
                $class = ReliableWrite::modelClass($type);
                // Owner scope prevents a guessed UUID from disclosing another account's objects.
                $query = $class::find()->where(['uuid' => $uuid, 'author_id' => $actor]);
                if ($type === 'meta') $query->andWhere(['prefab' => 0]);
                $models = $query->limit(2)->all();
                if (count($models) > 1) {
                    return array_merge($base, ['status' => 'conflict', 'reason' => 'ambiguous_uuid']);
                }
                if (count($models) === 1) {
                    $model = $models[0];
                    try {
                        $authorize($model);
                    } catch (\yii\web\ForbiddenHttpException) {
                        return array_merge($base, ['status' => 'indeterminate', 'reason' => 'permission_denied']);
                    }
                    return array_merge($base, [
                        'status' => 'observed', 'reason' => 'uuid_match_without_receipt',
                        'verification' => 'uuid_readback', 'id' => (int) $model->id,
                        'uuid' => $model->uuid, 'currentRevision' => ContentRevision::of($model),
                    ]);
                }
            }
            // No row can mean legacy creation, an in-flight transaction, wrong actor, or no request.
            // None of these reads authorizes replay or proves the object was never created.
            return array_merge($base, ['status' => 'not_observed', 'reason' => 'no_accessible_creation_evidence']);
        }));
    }
}
