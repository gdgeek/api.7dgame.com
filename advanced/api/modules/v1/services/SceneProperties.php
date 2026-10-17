<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Property;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use yii\db\Connection;
use yii\db\Exception as DbException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/** Scene associations reuse the global property dictionary without changing existing metadata. */
final class SceneProperties
{
    public static function listing($sceneId): array
    {
        $verse = Verse::findOne($sceneId);
        if ($verse === null) {
            throw new NotFoundHttpException('Verse not found');
        }

        return array_map([self::class, 'serialize'], $verse->getProperties()
            ->orderBy(['property.id' => SORT_ASC])->all());
    }

    public static function options($sceneId): array
    {
        if (!Verse::find()->where(['id' => $sceneId])->exists()) {
            throw new NotFoundHttpException('Verse not found');
        }

        return self::globalOptions();
    }

    public static function globalOptions(): array
    {
        return array_map([self::class, 'serialize'], Property::find()
            ->orderBy(['key' => SORT_ASC, 'id' => SORT_ASC])->all());
    }

    public static function add($sceneId, $body): array
    {
        if (is_array($body) && array_key_exists('propertyId', $body)) {
            if (!is_int($body['propertyId']) || $body['propertyId'] < 1) {
                throw new BadRequestHttpException('propertyId must be a positive integer');
            }
            if (array_key_exists('key', $body) || array_key_exists('info', $body)) {
                throw new BadRequestHttpException('Use propertyId or key with optional info, not both');
            }
            return self::associate($sceneId, $body['propertyId']);
        }

        if (!is_array($body) || !isset($body['key']) || !is_string($body['key'])) {
            throw new BadRequestHttpException('key must be a non-empty string of at most 255 characters');
        }
        $key = trim($body['key']);
        if ($key === '' || !mb_check_encoding($key, 'UTF-8') || mb_strlen($key, 'UTF-8') > 255) {
            throw new BadRequestHttpException('key must be a non-empty string of at most 255 characters');
        }
        $info = $body['info'] ?? null;
        if ($info !== null && (!is_string($info) || !mb_check_encoding($info, 'UTF-8') || strlen($info) > 65535)) {
            throw new BadRequestHttpException('info must be null or a string of at most 65535 bytes');
        }

        return self::associate($sceneId, null, $key, $info);
    }

    public static function remove($sceneId, $propertyId): array
    {
        return self::transaction(function (Connection $db) use ($sceneId, $propertyId): array {
            $property = self::lockProperty($db, ['id' => $propertyId]);
            self::lockScene($db, $sceneId);

            // Remove every legacy duplicate for this scene while preserving the global
            // dictionary entry and associations belonging to other scenes.
            VerseProperty::deleteAll(['verse_id' => $sceneId, 'property_id' => $property->id]);
            return self::serialize($property);
        });
    }

    public static function usage($sceneId, $propertyId): array
    {
        if (!Verse::find()->where(['id' => $sceneId])->exists()) {
            throw new NotFoundHttpException('Verse not found');
        }
        $property = Property::findOne($propertyId);
        if ($property === null) {
            throw new NotFoundHttpException('Property not found');
        }
        return ['property' => self::serialize($property), 'sceneCount' => self::sceneCount($property->id)];
    }

    public static function deleteGlobally($sceneId, $propertyId): array
    {
        return self::transaction(function (Connection $db) use ($sceneId, $propertyId): array {
            // Property management locks this row before a scene.
            // Hold that row until both the associations and dictionary are gone.
            $property = self::lockProperty($db, ['id' => $propertyId]);
            self::lockScene($db, $sceneId);
            $affectedSceneCount = self::sceneCount($property->id, $db);
            VerseProperty::deleteAll(['property_id' => $property->id]);
            $serialized = self::serialize($property);
            if ($property->delete() !== 1) {
                throw new ServerErrorHttpException('Property could not be deleted');
            }
            return ['property' => $serialized, 'affectedSceneCount' => $affectedSceneCount];
        });
    }

    private static function sceneCount($propertyId, ?Connection $db = null): int
    {
        if ($db !== null && $db->driverName === 'mysql') {
            $sceneIds = $db->createCommand('SELECT [[verse_id]] FROM {{%verse_property}} WHERE [[property_id]] = :id FOR UPDATE',
                [':id' => $propertyId])->queryColumn();
            return count(array_unique($sceneIds));
        }
        return (int) VerseProperty::find()->where(['property_id' => $propertyId])
            ->select('verse_id')->distinct()->count();
    }

    private static function associate($sceneId, ?int $propertyId, ?string $key = null, ?string $info = null): array
    {
        return self::transaction(function (Connection $db) use ($sceneId, $propertyId, $key, $info): array {
            if ($propertyId !== null) {
                $property = self::lockProperty($db, ['id' => $propertyId]);
            } else {
                // property.key has a unique index; an atomic insert also handles another
                // scene creating the same key, while retaining the existing info value.
                $db->createCommand()->upsert(Property::tableName(), ['key' => $key, 'info' => $info], false)->execute();
                $property = self::lockProperty($db, ['key' => $key]);
            }
            self::lockScene($db, $sceneId);

            $associated = $db->driverName === 'mysql'
                ? (bool) $db->createCommand('SELECT [[id]] FROM {{%verse_property}} WHERE [[verse_id]] = :sceneId AND [[property_id]] = :propertyId LIMIT 1 FOR UPDATE',
                    [':sceneId' => $sceneId, ':propertyId' => $property->id])->queryScalar()
                : VerseProperty::find()->where(['verse_id' => $sceneId, 'property_id' => $property->id])->exists();
            if (!$associated) {
                $association = new VerseProperty();
                $association->verse_id = $sceneId;
                $association->property_id = $property->id;
                // The property and scene were validated by current locking reads.
                // AR existence/unique validators use an older repeatable-read
                // snapshot and can falsely reject a concurrently recreated key.
                if (!$association->save(false)) {
                    throw new BadRequestHttpException('Property association could not be saved');
                }
            }

            return self::serialize($property);
        });
    }

    private static function transaction(callable $callback): mixed
    {
        $db = Verse::getDb();
        $nested = $db->getTransaction() !== null;
        for ($attempt = 1; ; $attempt++) {
            try {
                return $db->transaction($callback);
            } catch (DbException $error) {
                // A key upsert and deletion can contend on MySQL's unique and
                // primary indexes. Retry the whole rolled-back transaction,
                // never just its failed statement or a caller's outer transaction.
                if ($nested || $db->driverName !== 'mysql' || $attempt >= 3
                    || !in_array((int) ($error->errorInfo[1] ?? 0), [1205, 1213], true)) {
                    throw $error;
                }
                usleep(10000 * $attempt);
            }
        }
    }

    private static function lockProperty(Connection $db, array $condition, bool $required = true): ?Property
    {
        $query = Property::find()->where($condition);
        if ($db->driverName === 'mysql') {
            // A locking read sees a concurrent delete after waiting, rather than an
            // earlier repeatable-read snapshot that could recreate an orphan pivot.
            $command = $query->createCommand($db);
            $property = Property::findBySql($command->sql . ' FOR UPDATE', $command->params)->one();
        } else {
            $property = $query->one();
        }
        if ($property === null && $required) {
            throw new NotFoundHttpException('Property not found');
        }
        return $property;
    }

    private static function lockScene(Connection $db, $sceneId): void
    {
        // The legacy association table has no unique index. Mutations here lock
        // their property first, then this scene, preventing duplicate associations.
        if ($db->driverName === 'mysql') {
            $exists = $db->createCommand('SELECT [[id]] FROM {{%verse}} WHERE [[id]] = :id FOR UPDATE',
                [':id' => $sceneId])->queryScalar();
        } else {
            $exists = Verse::find()->where(['id' => $sceneId])->exists();
        }
        if (!$exists) {
            throw new NotFoundHttpException('Verse not found');
        }
    }

    private static function serialize(Property $property): array
    {
        return ['id' => (int) $property->id, 'key' => $property->key, 'info' => $property->info];
    }
}
