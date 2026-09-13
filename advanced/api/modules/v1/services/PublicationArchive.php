<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Verse;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/** Insert-only publication evidence; deliberately no ActiveRecord/CRUD mutation surface. */
final class PublicationArchive
{
    public const TABLE = '{{%scene_publication_revision}}';
    public const MAX_BYTES = 8 * 1024 * 1024;
    public const SCENE_BUDGET_BYTES = 512 * 1024 * 1024;
    public const WARNING_BYTES = 400 * 1024 * 1024;
    public const METADATA = ['publication_version_id', 'scene_id', 'snapshot_id', 'actor_id', 'operation_id',
        'source_server_revision', 'schema_version', 'language', 'content_hash', 'byte_length', 'created_at'];

    public static function assertTransactionalStorage(): void
    {
        $db = Verse::getDb();
        if ($db->driverName !== 'mysql') return; // SQLite uses an exclusive writer transaction in tests.
        $tables = ['verse', 'verse_code', 'code', 'meta', 'meta_code', 'verse_meta', 'meta_resource',
            'resource', 'file', 'manager', 'verse_space', 'space', 'snapshot', 'webmcp_operation', 'scene_publication_revision'];
        $tables = array_map(fn ($name) => $db->tablePrefix . $name, $tables);
        $rows = $db->createCommand('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->queryAll();
        $engines = array_column($rows, 'ENGINE', 'TABLE_NAME');
        foreach ($tables as $name) {
            if (strtolower($engines[$name] ?? '') !== 'innodb') {
                throw new HttpException(503, 'publication_storage_not_ready');
            }
        }
    }

    /** A separate format from P1 request digests. Stored bytes are the wire contract. */
    public static function encode(array $body): string
    {
        $canonical = json_encode(self::ordered($body, 0), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION, 64);
        if (strlen($canonical) > self::MAX_BYTES) throw new HttpException(413, 'publication_too_large');
        return $canonical;
    }

    private static function ordered(mixed $value, int $depth): mixed
    {
        if ($depth > 60) throw new BadRequestHttpException('publication_too_deep');
        if (is_object($value)) {
            $fields = get_object_vars($value);
            ksort($fields, SORT_STRING);
            foreach ($fields as &$item) $item = self::ordered($item, $depth + 1);
            return (object) $fields;
        }
        if (is_array($value)) {
            if (!array_is_list($value)) ksort($value, SORT_STRING);
            foreach ($value as &$item) $item = self::ordered($item, $depth + 1);
        }
        return $value;
    }

    public static function append(Verse $verse, int $snapshotId, string $canonical, string $language): array
    {
        $db = Verse::getDb();
        if (!$db->getTransaction()?->isActive) throw new \LogicException('Publication requires a transaction');
        $bytes = strlen($canonical);
        if ($bytes > self::MAX_BYTES) throw new HttpException(413, 'publication_too_large');
        $used = (int) (new Query())->cache(false)->from(self::TABLE)->where(['scene_id' => $verse->id])->sum('byte_length', $db);
        if ($used + $bytes > self::SCENE_BUDGET_BYTES) throw new HttpException(507, 'publication_capacity_exceeded');
        [$operation] = ReliableWrite::headers();
        $row = [
            'publication_version_id' => ReliableWrite::uuid(), 'scene_id' => (int) $verse->id,
            'snapshot_id' => $snapshotId, 'actor_id' => (int) Yii::$app->user->id, 'operation_id' => $operation,
            'source_server_revision' => ContentRevision::of($verse), 'schema_version' => 1, 'language' => $language,
            'canonical_body' => $canonical, 'content_hash' => 'sha256:' . hash('sha256', $canonical),
            'byte_length' => $bytes, 'created_at' => time(),
        ];
        $db->createCommand()->insert(self::TABLE, $row)->execute();
        return self::metadata($row);
    }

    public static function listing(int $sceneId, callable $authorize, int $limit = 20, int $before = 0): array
    {
        self::authorize($sceneId, $authorize);
        if ($limit < 1 || $limit > 50 || $before < 0) throw new BadRequestHttpException('Invalid history pagination');
        $db = Verse::getDb();
        return $db->useMaster(function () use ($sceneId, $limit, $before, $db) {
            $base = (new Query())->cache(false)->from(self::TABLE)->where(['scene_id' => $sceneId]);
            $stats = (clone $base)->select(['total' => 'COUNT(*)', 'bytes' => 'COALESCE(SUM(byte_length),0)'])->one($db);
            $query = (clone $base)->select(array_merge(['id'], self::METADATA));
            if ($before) $query->andWhere(['<', 'id', $before]);
            $rows = $query->orderBy(['id' => SORT_DESC])->limit($limit + 1)->all($db);
            $more = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            return ['sceneId' => $sceneId, 'items' => array_map([self::class, 'metadata'], $rows),
                'nextBefore' => $more ? (int) end($rows)['id'] : null,
                'historyStatus' => (int) $stats['total'] === 0 ? 'history_unavailable' : 'available',
                'total' => (int) $stats['total'], 'totalBytes' => (int) $stats['bytes'],
                'maxBodyBytes' => self::MAX_BYTES, 'sceneBudgetBytes' => self::SCENE_BUDGET_BYTES,
                'capacityWarning' => (int) $stats['bytes'] >= self::WARNING_BYTES,
                'resourceBytesArchived' => false];
        });
    }

    public static function read(int $sceneId, string $version, callable $authorize): array
    {
        self::authorize($sceneId, $authorize);
        if (!ReliableWrite::validId($version)) throw new BadRequestHttpException('Invalid publication version');
        $db = Verse::getDb();
        $row = $db->useMaster(fn () => (new Query())->cache(false)->from(self::TABLE)->where([
            'scene_id' => $sceneId, 'publication_version_id' => strtolower($version),
        ])->one($db));
        if (!$row) throw new NotFoundHttpException('publication_version_not_found');
        $body = $row['canonical_body'];
        try { $parsed = json_decode($body, true, 64, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new HttpException(500, 'publication_corrupt'); }
        if (strlen($body) > self::MAX_BYTES || strlen($body) !== (int) $row['byte_length'] ||
            !hash_equals($row['content_hash'], 'sha256:' . hash('sha256', $body)) ||
            (int) $row['schema_version'] !== 1 || ($parsed['schemaVersion'] ?? null) !== 1 ||
            ($parsed['scene']['id'] ?? null) !== $sceneId || ($parsed['language'] ?? null) !== $row['language'] ||
            !in_array($row['language'], ['lua', 'js'], true) || !is_array($parsed['runtime'] ?? null) ||
            !is_string($parsed['runtime']['data'] ?? null) || !is_string($parsed['runtime']['code'] ?? null) ||
            !is_array($parsed['runtime']['metas'] ?? null) || !is_array($parsed['runtime']['resources'] ?? null) ||
            !is_array($parsed['runtime']['managers'] ?? null) || ($parsed['dependencies']['resourceBytesArchived'] ?? null) !== false) {
            throw new HttpException(500, 'publication_corrupt');
        }
        return self::metadata($row) + ['canonicalBody' => $body, 'integrity' => 'verified', 'resourceBytesArchived' => false];
    }

    private static function authorize(int $id, callable $authorize): void
    {
        Verse::getDb()->useMaster(function () use ($id, $authorize) {
            $verse = Verse::find()->where(["id" => $id])->cache(false)->one();
            if (!$verse) throw new NotFoundHttpException('Scene not found');
            $authorize($verse);
        });
    }

    public static function metadata(array $row): array
    {
        $result = [];
        foreach (self::METADATA as $name) {
            $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
            $result[$key] = in_array($name, ['scene_id', 'snapshot_id', 'actor_id', 'schema_version', 'byte_length', 'created_at'])
                ? (int) $row[$name] : $row[$name];
        }
        return $result;
    }
}
