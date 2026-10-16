<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Property;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use yii\db\Connection;
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

    public static function add($sceneId, $body): array
    {
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

        return Verse::getDb()->transaction(function (Connection $db) use ($sceneId, $key, $info): array {
            // The legacy association table has no database unique index. Serialize writers
            // for the same scene so simultaneous adds through this API stay idempotent.
            if ($db->driverName === 'mysql') {
                $exists = $db->createCommand('SELECT [[id]] FROM {{%verse}} WHERE [[id]] = :id FOR UPDATE',
                    [':id' => $sceneId])->queryScalar();
            } else {
                $exists = Verse::find()->where(['id' => $sceneId])->exists();
            }
            if (!$exists) {
                throw new NotFoundHttpException('Verse not found');
            }

            // property.key has a unique index; an atomic insert also handles another
            // scene creating the same key, while retaining the existing info value.
            $db->createCommand()->upsert(Property::tableName(), ['key' => $key, 'info' => $info], false)->execute();
            $property = Property::findOne(['key' => $key]);
            if ($property === null) {
                throw new ServerErrorHttpException('Property could not be reloaded');
            }

            if (!VerseProperty::find()->where(['verse_id' => $sceneId, 'property_id' => $property->id])->exists()) {
                $association = new VerseProperty();
                $association->verse_id = $sceneId;
                $association->property_id = $property->id;
                if (!$association->save()) {
                    throw new BadRequestHttpException('Property association could not be saved');
                }
            }

            return self::serialize($property);
        });
    }

    private static function serialize(Property $property): array
    {
        return ['id' => (int) $property->id, 'key' => $property->key, 'info' => $property->info];
    }
}
