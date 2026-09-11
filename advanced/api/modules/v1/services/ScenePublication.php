<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

final class ScenePublication
{
    /** Only persisted runtime columns; current scene title/image must not rewrite history. */
    public static function storageHash(Snapshot $snapshot): string
    {
        $data = $snapshot->getAttributes(['id', 'verse_id', 'uuid', 'code', 'data', 'metas', 'resources', 'space', 'managers']);
        foreach (['data', 'metas', 'resources', 'space', 'managers'] as $field) {
            if (is_string($data[$field] ?? null)) {
                try {
                    $data[$field] = json_decode($data[$field], false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    // Hash malformed legacy values faithfully; do not invent valid content.
                }
            }
        }
        return ContentRevision::hash($data);
    }

    /** Called inside ReliableWrite's owner-row transaction. */
    public static function publish(Verse $verse): array
    {
        $sourceRevision = ContentRevision::of($verse);
        $snapshot = Snapshot::CreateById($verse->id);
        if (!$snapshot->save()) {
            throw new BadRequestHttpException('Snapshot validation or save failed');
        }
        if (!$snapshot->refresh()) {
            throw new ServerErrorHttpException('Snapshot could not be reloaded');
        }
        $payload = $snapshot->toArray([], Snapshot::TAKE_PHOTO_EXTRA_FIELDS);
        // Managers are a runtime field even though older take-photo omitted the expansion.
        $payload['managers'] = $snapshot->toArray([], ['managers'])['managers'] ?? null;
        $canonical = ContentRevision::canonical($payload);
        $version = ReliableWrite::uuid();
        $hash = 'sha256:' . hash('sha256', $canonical);
        Yii::$app->db->createCommand()->insert('{{%scene_publication_revision}}', [
            'revision' => $version, 'verse_id' => $verse->id,
            'snapshot_id' => $snapshot->id, 'snapshot_uuid' => $snapshot->uuid,
            'source_revision' => $sourceRevision, 'content_hash' => $hash,
            'snapshot_hash' => self::storageHash($snapshot),
            'snapshot_json' => $canonical, 'created_by' => (int) Yii::$app->user->id,
            'created_at' => time(),
        ])->execute();
        return array_merge($payload, [
            'snapshotId' => (int) $snapshot->id,
            'publicationRevision' => $version, 'contentHash' => $hash,
        ]);
    }

    public static function read(int $sceneId, ?string $revision, callable $authorize): array
    {
        $verse = Verse::findOne($sceneId);
        if ($verse === null) {
            throw new NotFoundHttpException('Scene not found');
        }
        $authorize($verse);
        if ($revision !== null) {
            if (!ReliableWrite::validId($revision)) {
                throw new BadRequestHttpException('Invalid publication revision');
            }
            $row = (new Query())->from('{{%scene_publication_revision}}')->where(['verse_id' => $sceneId, 'revision' => strtolower($revision)])->one();
            if ($row === false) {
                throw new NotFoundHttpException('Publication revision not found');
            }
            if (!hash_equals($row['content_hash'], 'sha256:' . hash('sha256', $row['snapshot_json']))) {
                throw new ServerErrorHttpException('Publication integrity check failed');
            }
            return array_merge(self::summary($row), [
                'snapshot' => json_decode($row['snapshot_json'], false, 512, JSON_THROW_ON_ERROR),
            ]);
        }
        $snapshot = Snapshot::find()->where(['verse_id' => $sceneId])->orderBy(['id' => SORT_DESC])->one();
        if ($snapshot === null) {
            return ['sceneId' => $sceneId, 'published' => false, 'snapshotId' => null, 'snapshotUuid' => null, 'publicationRevision' => null, 'contentHash' => null];
        }
        $hash = self::storageHash($snapshot);
        $row = (new Query())->from('{{%scene_publication_revision}}')->where([
            'verse_id' => $sceneId, 'snapshot_id' => $snapshot->id, 'snapshot_hash' => $hash,
        ])->orderBy(['id' => SORT_DESC])->one();
        if ($row !== false) {
            return self::summary($row);
        }
        // A pre-migration or externally overwritten snapshot has no immutable archive.
        return ['sceneId' => $sceneId, 'published' => true, 'snapshotId' => (int) $snapshot->id, 'snapshotUuid' => $snapshot->uuid, 'publicationRevision' => null, 'contentHash' => null, 'verification' => 'legacy_snapshot'];
    }

    private static function summary(array $row): array
    {
        return [
            'sceneId' => (int) $row['verse_id'], 'published' => true,
            'snapshotId' => (int) $row['snapshot_id'], 'snapshotUuid' => $row['snapshot_uuid'],
            'publicationRevision' => $row['revision'], 'contentHash' => $row['content_hash'],
            'sourceRevision' => $row['source_revision'], 'publishedAt' => (int) $row['created_at'],
        ];
    }
}
