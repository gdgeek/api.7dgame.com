<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/** P1 uses the existing mutable Snapshot; historical publication versions are P2. */
final class ScenePublication
{
    /** Called inside ReliableWrite's owner-row transaction. */
    public static function publish(Verse $verse): array
    {
        $snapshot = Snapshot::CreateById($verse->id);
        if (!$snapshot->save()) {
            throw new BadRequestHttpException('Snapshot validation or save failed');
        }
        if (!$snapshot->refresh()) {
            throw new ServerErrorHttpException('Snapshot could not be reloaded');
        }
        $payload = $snapshot->toArray([], Snapshot::TAKE_PHOTO_EXTRA_FIELDS);
        return array_merge($payload, ['snapshotId' => (int) $snapshot->id]);
    }

    public static function read(int $sceneId, callable $authorize): array
    {
        $verse = Verse::findOne($sceneId);
        if ($verse === null) {
            throw new NotFoundHttpException('Scene not found');
        }
        $authorize($verse);
        $snapshot = Snapshot::find()->where(['verse_id' => $sceneId])->orderBy(['id' => SORT_DESC])->one();
        // This describes the current pointer, not the contents of a past operation.
        return [
            'sceneId' => $sceneId,
            'published' => $snapshot !== null,
            'snapshotId' => $snapshot === null ? null : (int) $snapshot->id,
            'snapshotUuid' => $snapshot?->uuid,
            'verification' => 'current_snapshot',
        ];
    }
}
