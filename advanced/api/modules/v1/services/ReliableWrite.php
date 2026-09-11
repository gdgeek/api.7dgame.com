<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Meta;
use api\modules\v1\models\Verse;
use Yii;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/** One owner-row lock covers editor data, code and publication, including legacy callers. */
final class ReliableWrite
{
    public static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }

    public static function validId(mixed $value): bool
    {
        return is_string($value) && (bool) preg_match('/\A[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}\z/iD', $value);
    }

    /** @return array{?string,?string} */
    public static function headers(): array
    {
        $headers = Yii::$app->request->headers;
        $operation = $headers->get('Idempotency-Key');
        $match = $headers->get('If-Match');
        if ($operation === null && $match === null) {
            return [null, null];
        }
        if (!self::validId($operation) || !is_string($match) || !preg_match('/\A"(sha256:[a-f0-9]{64})"\z/D', $match, $parts)) {
            throw new BadRequestHttpException('Use paired UUID Idempotency-Key and quoted serverRevision If-Match headers');
        }
        return [strtolower($operation), $parts[1]];
    }

    private static function actor(): int
    {
        $actor = (int) Yii::$app->user->id;
        if ($actor <= 0) {
            throw new UnauthorizedHttpException('Authentication required');
        }
        return $actor;
    }

    public static function modelClass(string $type): string
    {
        return match ($type) {
            'verse' => Verse::class,
            'meta' => Meta::class,
            default => throw new BadRequestHttpException('Invalid write target'),
        };
    }

    public static function run(string $type, int $id, string $action, array $body, callable $authorize, callable $mutate): array
    {
        [$operationId, $expectedRevision] = self::headers();
        $actorId = self::actor();
        $class = self::modelClass($type);
        $db = $class::getDb();
        $transaction = $db->beginTransaction();
        try {
            $table = $db->quoteTableName($class::tableName());
            if ($db->driverName === 'sqlite') {
                // Acquire SQLite's writer lock before the read (also exercised by tests).
                $db->createCommand("UPDATE $table SET id = id WHERE id = :id", [':id' => $id])->execute();
            }
            $row = $db->createCommand("SELECT * FROM $table WHERE id = :id" . ($db->driverName === 'sqlite' ? '' : ' FOR UPDATE'), [':id' => $id])->queryOne();
            if ($row === false) {
                throw new NotFoundHttpException('Write target not found');
            }
            /** @var ActiveRecord $model */
            $model = $class::instantiate($row);
            $class::populateRecord($model, $row);
            $model->afterFind();
            $authorize($model);
            $fingerprint = ContentRevision::hash([$type, $id, $action, $expectedRevision, $body]);
            if ($operationId !== null) {
                $previous = (new Query())->from('{{%webmcp_operation}}')->where(['actor_id' => $actorId, 'operation_id' => $operationId])->one($db);
                if ($previous !== false) {
                    if (!hash_equals($previous['request_hash'], $fingerprint)) {
                        throw new ConflictHttpException('Operation ID was already used for a different request');
                    }
                    $receipt = json_decode($previous['receipt'], true, 512, JSON_THROW_ON_ERROR);
                    $transaction->commit();
                    return self::replay($receipt);
                }
                if (!hash_equals(ContentRevision::of($model), $expectedRevision)) {
                    throw new ConflictHttpException('Content changed on the server. Reload and review before saving or publishing.');
                }
            }
            $result = $mutate($model);
            if (!is_array($result)) {
                throw new \LogicException('A write must return an acknowledgment');
            }
            if (!$model->refresh()) {
                throw new \RuntimeException('Unable to reload saved target');
            }
            $revision = ContentRevision::of($model);
            $result['serverRevision'] = $revision;
            if ($operationId !== null) {
                $receipt = [
                    'operationId' => $operationId,
                    'status' => 'completed',
                    'targetType' => $type,
                    'targetId' => $id,
                    'action' => $action,
                    'serverRevision' => $revision,
                ];
                foreach (['snapshotId'] as $field) {
                    if (isset($result[$field])) {
                        $receipt[$field] = $result[$field];
                    }
                }
                // Deliberate allowlist: no tokens, proposal bodies or signed resource URLs.
                $db->createCommand()->insert('{{%webmcp_operation}}', [
                    'actor_id' => $actorId, 'operation_id' => $operationId,
                    'target_type' => $type, 'target_id' => $id, 'action' => $action,
                    'request_hash' => $fingerprint, 'receipt' => ContentRevision::canonical($receipt),
                    'created_at' => time(),
                ])->execute();
                $result['writeReceipt'] = $receipt;
            }
            $transaction->commit();
            return $result;
        } catch (\Throwable $error) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            // A cross-target race on the actor's key cannot commit either side twice.
            if ($error instanceof IntegrityException && $operationId !== null && (new Query())->from('{{%webmcp_operation}}')->where(['actor_id' => $actorId, 'operation_id' => $operationId])->exists($db)) {
                throw new ConflictHttpException('Operation ID was already used; query its receipt before retrying');
            }
            throw $error;
        }
    }

    private static function replay(array $receipt): array
    {
        return array_merge([
            'id' => $receipt['snapshotId'] ?? $receipt['targetId'],
            'serverRevision' => $receipt['serverRevision'],
            'writeReceipt' => $receipt,
            'replayed' => true,
        ], array_intersect_key($receipt, array_flip(['snapshotId'])));
    }

    public static function receipt(string $type, int $id, string $operationId, callable $authorize): array
    {
        if (!self::validId($operationId)) {
            throw new BadRequestHttpException('Invalid operation ID');
        }
        $class = self::modelClass($type);
        $model = $class::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Write target not found');
        }
        $authorize($model);
        $row = (new Query())->from('{{%webmcp_operation}}')->where([
            'actor_id' => self::actor(), 'operation_id' => strtolower($operationId),
            'target_type' => $type, 'target_id' => $id,
        ])->one();
        if ($row === false) {
            // Absence can race an uncommitted request. It never proves rollback.
            throw new NotFoundHttpException('Operation not observed; this does not prove it failed');
        }
        return json_decode($row['receipt'], true, 512, JSON_THROW_ON_ERROR);
    }
}
