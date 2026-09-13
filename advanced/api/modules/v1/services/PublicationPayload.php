<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Resource;
use api\modules\v1\models\Space;
use api\modules\v1\models\Verse;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/** Single MVCC capture. Raw queries deliberately avoid AR afterFind writes and caches. */
final class PublicationPayload
{
    public static function capture(Verse $verse): array
    {
        $data = self::decode($verse->data);
        $modules = is_object($data) ? ($data->children->modules ?? null) : null;
        if (!is_array($modules) || $modules === []) {
            throw new BadRequestHttpException('The scene is empty. Add at least one entity before publishing.');
        }
        $metaIds = array_values(array_unique(array_filter(array_map(
            static fn ($module) => (int) ($module->parameters->meta_id ?? 0), $modules))));
        $metas = [];
        foreach (self::query('meta')->where(['id' => $metaIds])->orderBy('id')->all() as $meta) {
            $metas[] = [
                'id' => (int) $meta['id'], 'uuid' => $meta['uuid'], 'title' => $meta['title'],
                'prefab' => (int) $meta['prefab'], 'type' => $meta['prefab'] == 0 ? 'entity' : 'prefab',
                'data' => self::json(self::decode($meta['data'])),
                'events' => self::json(self::decode($meta['events'])),
                'code' => self::code('meta', (int) $meta['id']),
            ];
        }
        if (count($metas) !== count($metaIds)) {
            throw new BadRequestHttpException('A scene entity is missing; review dependencies before publishing');
        }
        $resources = [];
        $resourceRows = self::query('resource')->from(['r' => '{{%resource}}'])->select('r.*')->distinct()
            ->innerJoin(['mr' => '{{%meta_resource}}'], 'mr.resource_id = r.id')
            ->innerJoin(['vm' => '{{%verse_meta}}'], 'vm.meta_id = mr.meta_id')
            ->where(['vm.verse_id' => $verse->id])->orderBy('r.id')->all();
        foreach ($resourceRows as $row) {
            $info = self::decode($row['info']);
            if ($row['type'] === 'polygen' && ($info === null || $info === '' || $info == (object) [] || $info === [])) {
                $info = json_decode(Resource::DEFAULT_POLYGEN_INFO);
            }
            $resources[] = [
                'id' => (int) $row['id'], 'name' => $row['name'], 'uuid' => $row['uuid'],
                'type' => $row['type'], 'info' => is_string($info) || $info === null ? $info : self::json($info),
                'created_at' => $row['created_at'], 'image_id' => $row['image_id'],
                'file' => self::file($row['file_id']), 'image' => self::file($row['image_id']),
            ];
        }
        $managers = [];
        foreach (self::query('manager')->where(['verse_id' => $verse->id])->orderBy('id')->all() as $row) {
            $managers[] = ['type' => $row['type'], 'data' => self::json(self::decode($row['data']))];
        }
        $link = self::query('verse_space')->where(['verse_id' => $verse->id])->one();
        $space = $link ? self::query('space')->where(['id' => $link['space_id']])->one() : false;
        $runtime = [
            'uuid' => $verse->uuid, 'code' => self::code('verse', (int) $verse->id),
            'data' => self::json($data), 'metas' => $metas, 'resources' => $resources, 'managers' => $managers,
            'space' => $space ? [
                'type' => Space::compactData($space['data'], $space['image_id'] ? (int) $space['image_id'] : null)['provider'] ?? null,
                'image' => self::file($space['image_id']), 'mesh' => self::file($space['mesh_id']), 'file' => self::file($space['file_id']),
            ] : null,
        ];
        $image = self::file($verse->image_id);
        $archivedRuntime = $runtime;
        foreach ($archivedRuntime['resources'] as &$resource) {
            $resource['file'] = self::reference($resource['file']);
            $resource['image'] = self::reference($resource['image']);
        }
        unset($resource);
        if ($archivedRuntime['space'] !== null) {
            foreach (['image', 'mesh', 'file'] as $name) {
                $archivedRuntime['space'][$name] = self::reference($archivedRuntime['space'][$name]);
            }
        }
        return [
            'runtime' => $runtime, 'image' => $image,
            'body' => [
                'schemaVersion' => 1, 'language' => Yii::$app->request->get('cl', 'lua'),
                'scene' => ['id' => (int) $verse->id, 'uuid' => $verse->uuid, 'name' => $verse->name,
                    'description' => $verse->description ?? '', 'authorId' => (int) $verse->author_id,
                    'image' => self::reference($image)],
                'runtime' => $archivedRuntime,
                'dependencies' => ['spaceId' => $space ? (int) $space['id'] : null, 'versions' => null,
                    'resourceBytesArchived' => false, 'fileAccess' => 'resolve_current_authorized_file'],
            ],
        ];
    }

    private static function query(string $table): Query
    {
        return (new Query())->from('{{%' . $table . '}}')->cache(false);
    }

    private static function code(string $type, int $id): string
    {
        $language = Yii::$app->request->get('cl');
        $prefix = $language ? '' : ($type === 'verse' ? "local verse = {}\n local is_playing = false\n" : "local meta = {}\nlocal index = ''\n");
        $language = $language ?: 'lua';
        $row = self::query($type . '_code')->where([$type . '_id' => $id])->one();
        if ($row && $row['lua'] === null && $row['js'] === null && $row['code_id']) {
            $row = self::query('code')->where(['id' => $row['code_id']])->one();
        }
        $script = $row[$language] ?? '';
        return str_contains($script, $prefix) ? $script : $prefix . $script;
    }

    private static function file($id): ?array
    {
        if (!$id) return null;
        $row = self::query('file')->select(['id', 'md5', 'type', 'url', 'filename', 'size', 'key'])->where(['id' => $id])->one();
        if (!$row) throw new BadRequestHttpException('A referenced file is missing; review resources before publishing');
        $row['id'] = (int) $row['id'];
        if ($row['size'] !== null) $row['size'] = (int) $row['size'];
        // Preserve the existing local-runtime URL substitution, without fetching remote bytes.
        if (preg_match('/^https?:\/\/(\d+\.\d+\.\d+\.\d+)(?::\d+)?/', Yii::$app->request->hostInfo, $match)) {
            $row['url'] = str_replace('[ip]', $match[1], $row['url']);
        }
        return $row;
    }

    private static function reference(?array $file): ?array
    {
        if ($file === null) return null;
        unset($file['url']); // Version 1 stores a file reference, never an expiring access URL.
        return $file;
    }

    private static function decode(mixed $value): mixed
    {
        if (is_string($value)) {
            try { return json_decode($value, false, 64, JSON_THROW_ON_ERROR); }
            catch (\JsonException) { return $value; }
        }
        if (is_array($value)) return json_decode(self::json($value), false, 64, JSON_THROW_ON_ERROR);
        return $value;
    }

    private static function json(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES, 64);
    }
}
