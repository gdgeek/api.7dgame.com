<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/** Current runtime pointer and immutable publication evidence share a single capture. */
final class ScenePublication
{
    /** Called inside ReliableWrite's owner-row transaction. */
    public static function publish(Verse $verse): array
    {
        if (!Verse::getDb()->getTransaction()?->isActive) {
            throw new \LogicException('Publish through ReliableWrite');
        }
        $capture = PublicationPayload::capture($verse);
        $canonical = PublicationArchive::encode($capture['body']);
        $snapshot = Snapshot::find()->where(['verse_id' => $verse->id])->one() ?? new Snapshot();
        $snapshot->verse_id = $verse->id;
        foreach ($capture['runtime'] as $field => $value) $snapshot->$field = $value;
        if (!$snapshot->save()) {
            throw new BadRequestHttpException('Snapshot validation or save failed');
        }
        $archive = PublicationArchive::append($verse, (int) $snapshot->id, $canonical, $capture['body']['language']);
        // Return the same captured values; never re-query mutable Verse relationships here.
        return $capture['runtime'] + [
            'id' => (int) $snapshot->id, 'snapshotId' => (int) $snapshot->id,
            'name' => $verse->name, 'description' => $verse->description ?? '', 'image' => $capture['image'],
            'publicationVersionId' => $archive['publicationVersionId'], 'contentHash' => $archive['contentHash'],
            'schemaVersion' => $archive['schemaVersion'], 'language' => $archive['language'],
        ];
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
