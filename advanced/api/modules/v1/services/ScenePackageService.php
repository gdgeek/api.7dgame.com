<?php

namespace api\modules\v1\services;

use api\modules\v1\helpers\IdRemapper;
use api\modules\v1\models\File;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaCode;
use api\modules\v1\models\MetaResource;
use api\modules\v1\models\Resource;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseCode;
use api\modules\v1\models\Version;
use api\modules\v1\models\VerseMeta;
use common\components\UuidHelper;
use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

/**
 * 场景包服务层
 *
 * 封装场景导出和导入的核心业务逻辑。
 */
class ScenePackageService extends Component
{
    /**
     * 构建导出数据树
     *
     * 查询 Verse 及所有关联数据，组装 Scene_Data_Tree 结构。
     *
     * @param int $verseId 场景 ID
     * @return array Scene_Data_Tree 结构
     * @throws NotFoundHttpException 当 Verse 不存在时
     */
    public function buildExportData(int $verseId): array
    {
        // 查询 Verse
        $verse = Verse::findOne($verseId);
        if ($verse === null) {
            throw new NotFoundHttpException('Verse not found');
        }

        // 构建 verse 部分
        $verseData = $this->buildVerseData($verse);

        // 查询所有关联 Metas（通过 VerseMeta）
        $metas = $verse->getMetas()->all();

        // 构建 metas 部分
        $metasData = [];
        foreach ($metas as $meta) {
            $metasData[] = $this->buildMetaData($meta);
        }

        // 查询所有关联 Resources（通过 VerseMeta -> MetaResource）
        $resources = $verse->getResources();

        // 构建 resources 部分
        $resourcesData = [];
        foreach ($resources as $resource) {
            $resourcesData[] = $this->buildResourceData($resource);
        }

        // 查询 MetaResource 关联关系构建 metaResourceLinks
        $metaIds = array_map(function ($meta) {
            return $meta->id;
        }, $metas);

        $metaResourceLinks = [];
        if (!empty($metaIds)) {
            $metaResources = MetaResource::find()
                ->where(['meta_id' => $metaIds])
                ->all();

            foreach ($metaResources as $mr) {
                $metaResourceLinks[] = [
                    'meta_id' => $mr->meta_id,
                    'resource_id' => $mr->resource_id,
                ];
            }
        }

        // 组装并返回 Scene_Data_Tree 结构
        return [
            'verse' => $verseData,
            'metas' => $metasData,
            'resources' => $resourcesData,
            'metaResourceLinks' => $metaResourceLinks,
        ];
    }

    /**
     * 执行场景导入（事务内）
     *
     * 在单个数据库事务中按顺序创建所有实体、建立关联、执行 ID 重映射。
     * 任何步骤失败将抛出异常触发事务回滚。
     *
     * @param array $data 解析后的场景数据
     * @return array {verseId, metaIdMap, resourceIdMap}
     * @throws \Exception 任何失败将触发事务回滚
     */
    public function importScene(array $data): array
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $verseData = $data['verse'];
            $metasData = $data['metas'] ?? [];
            $resourceFileMappings = $data['resourceFileMappings'] ?? [];
            $exportedResources = $data['resources'] ?? [];
            $exportedMetaResourceLinks = $data['metaResourceLinks'] ?? [];
            $fileIdMap = []; // original File ID => new File ID

            // --- Step 0: Convert exported resources format to import format ---
            // When importing from ZIP (exported data), resources come as 'resources' array
            // with file data embedded. Convert them to resourceFileMappings format by
            // creating File records first, then building the mappings.
            $resourceOriginalIdMap = []; // original resource ID => originalUuid (for numeric ID remap)
            if (!empty($exportedResources) && empty($resourceFileMappings)) {
                foreach ($exportedResources as $res) {
                    // Create File record from exported file data
                    $fileId = null;
                    if (!empty($res['file'])) {
                        $fileRecord = $this->createFileFromImageData($res['file']);
                        $fileId = $fileRecord->id;
                        if (isset($res['file']['id'])) {
                            $fileIdMap[$res['file']['id']] = $fileRecord->id;
                        }
                    }

                    $mapping = [
                        'originalUuid' => $res['uuid'],
                        'fileId' => $fileId,
                        'name' => $res['name'],
                        'type' => $res['type'],
                        'info' => $res['info'],
                        'image' => $res['image'] ?? null,
                    ];
                    if (isset($res['id'])) {
                        $mapping['originalId'] = $res['id'];
                    }
                    $resourceFileMappings[] = $mapping;
                }

                // Convert metaResourceLinks to metas[].resourceFileIds
                // metaResourceLinks: [{meta_id: 101, resource_id: 201}, ...]
                // We need to map resource_id -> fileId for each meta
                if (!empty($exportedMetaResourceLinks)) {
                    // Build resource original ID -> fileId map
                    $resIdToFileId = [];
                    foreach ($exportedResources as $i => $res) {
                        if (isset($res['id'], $resourceFileMappings[$i]['fileId'])) {
                            $resIdToFileId[$res['id']] = $resourceFileMappings[$i]['fileId'];
                        }
                    }
                    // Build meta original ID -> resourceFileIds
                    $metaResourceFileIds = [];
                    foreach ($exportedMetaResourceLinks as $link) {
                        $metaId = $link['meta_id'];
                        $resId = $link['resource_id'];
                        if (isset($resIdToFileId[$resId])) {
                            $metaResourceFileIds[$metaId][] = $resIdToFileId[$resId];
                        }
                    }
                    // Inject resourceFileIds into metasData
                    foreach ($metasData as &$metaInput) {
                        if (isset($metaInput['id'], $metaResourceFileIds[$metaInput['id']])) {
                            $metaInput['resourceFileIds'] = $metaResourceFileIds[$metaInput['id']];
                        }
                    }
                    unset($metaInput);
                }
            }

            // --- Step 1: Create Resources (new UUID, file_id from resourceFileMappings) ---
            $resourceIdMap = []; // originalUuid => new Resource ID
            $fileIdToResourceId = []; // fileId => new Resource ID

            foreach ($resourceFileMappings as $mapping) {
                $resource = new Resource();
                $resource->uuid = UuidHelper::uuid();
                $resource->name = $mapping['name'] . '（副本 ' . date('Y-m-d H:i:s') . '）';
                $resource->type = $mapping['type'];
                $resource->info = $mapping['info'];
                $resource->file_id = $mapping['fileId'];

                if (!$resource->save()) {
                    throw new \Exception('Failed to create Resource: ' . json_encode($resource->getErrors()));
                }

                // --- Step 1.1: Handle resource.image (create File and set image_id) ---
                if (!empty($mapping['image'])) {
                    $resourceImageFile = $this->createFileFromImageData($mapping['image']);
                    $resource->image_id = $resourceImageFile->id;
                    if (isset($mapping['image']['id'])) {
                        $fileIdMap[$mapping['image']['id']] = $resourceImageFile->id;
                    }
                    if (!$resource->save()) {
                        throw new \Exception('Failed to update Resource image_id: ' . json_encode($resource->getErrors()));
                    }
                }

                $resourceIdMap[$mapping['originalUuid']] = $resource->id;
                $fileIdToResourceId[$mapping['fileId']] = $resource->id;
            }

            // --- Step 2: Create Verse (new UUID, data temporarily empty) ---
            $verse = new Verse();
            $verse->uuid = UuidHelper::uuid();
            $verse->name = $verseData['name'] . '（副本 ' . date('Y-m-d H:i:s') . '）';
            $verse->description = $verseData['description'] ?? null;
            // Save with empty data first to avoid afterSave refreshMetas issues
            $verse->data = null;

            if (!$verse->save()) {
                throw new \Exception('Failed to create Verse: ' . json_encode($verse->getErrors()));
            }

            // --- Step 2.1: Handle verse.image (create File and set image_id) ---
            if (!empty($verseData['image'])) {
                $verseImageFile = $this->createFileFromImageData($verseData['image']);
                $verse->image_id = $verseImageFile->id;
                if (isset($verseData['image']['id'])) {
                    $fileIdMap[$verseData['image']['id']] = $verseImageFile->id;
                }
                if (!$verse->save()) {
                    throw new \Exception('Failed to update Verse image_id: ' . json_encode($verse->getErrors()));
                }
            }

            // --- Step 3: Create VerseCode (if present) ---
            if (!empty($verseData['verseCode'])) {
                $verseCode = new VerseCode();
                $verseCode->verse_id = $verse->id;
                $verseCode->blockly = $verseData['verseCode']['blockly'] ?? null;
                $verseCode->lua = $verseData['verseCode']['lua'] ?? null;
                $verseCode->js = $verseData['verseCode']['js'] ?? null;

                if (!$verseCode->save()) {
                    throw new \Exception('Failed to create VerseCode: ' . json_encode($verseCode->getErrors()));
                }
            }

            // --- Step 4: Create Metas (new UUID, data/events temporarily empty) and VerseMeta associations ---
            $metaIdMap = []; // originalUuid => new Meta ID
            $metaOriginalData = []; // new Meta ID => original data/events from input

            foreach ($metasData as $metaInput) {
                $meta = new Meta();
                $meta->uuid = UuidHelper::uuid();
                $meta->title = $metaInput['title'] . '（副本 ' . date('Y-m-d H:i:s') . '）';
                $meta->prefab = $metaInput['prefab'] ?? 0;
                // Save with empty data/events first to avoid afterSave refreshResources issues
                $meta->data = null;
                $meta->events = null;

                if (!$meta->save()) {
                    throw new \Exception('Failed to create Meta: ' . json_encode($meta->getErrors()));
                }

                // --- Step 4.1: Handle meta.image (create File and set image_id) ---
                if (!empty($metaInput['image'])) {
                    $metaImageFile = $this->createFileFromImageData($metaInput['image']);
                    $meta->image_id = $metaImageFile->id;
                    if (isset($metaInput['image']['id'])) {
                        $fileIdMap[$metaInput['image']['id']] = $metaImageFile->id;
                    }
                    if (!$meta->save()) {
                        throw new \Exception('Failed to update Meta image_id: ' . json_encode($meta->getErrors()));
                    }
                }

                $metaIdMap[$metaInput['uuid']] = $meta->id;

                // Store original data/events for later remapping
                $metaOriginalData[$meta->id] = [
                    'data' => $metaInput['data'] ?? null,
                    'events' => $metaInput['events'] ?? null,
                    'resourceFileIds' => $metaInput['resourceFileIds'] ?? [],
                    'metaCode' => $metaInput['metaCode'] ?? null,
                ];

                // Create VerseMeta association
                $verseMeta = new VerseMeta();
                $verseMeta->verse_id = $verse->id;
                $verseMeta->meta_id = $meta->id;

                if (!$verseMeta->save()) {
                    throw new \Exception('Failed to create VerseMeta: ' . json_encode($verseMeta->getErrors()));
                }
            }

            // --- Step 5: Create MetaCodes (if present) ---
            foreach ($metaOriginalData as $metaId => $originalData) {
                if (!empty($originalData['metaCode'])) {
                    $metaCode = new MetaCode();
                    $metaCode->meta_id = $metaId;
                    $metaCode->blockly = $originalData['metaCode']['blockly'] ?? null;
                    $metaCode->lua = $originalData['metaCode']['lua'] ?? null;
                    $metaCode->js = $originalData['metaCode']['js'] ?? null;

                    if (!$metaCode->save()) {
                        throw new \Exception('Failed to create MetaCode: ' . json_encode($metaCode->getErrors()));
                    }
                }
            }

            // --- Step 6: Create MetaResource associations (through resourceFileIds and fileId mapping) ---
            foreach ($metaOriginalData as $metaId => $originalData) {
                $resourceFileIds = $originalData['resourceFileIds'] ?? [];
                foreach ($resourceFileIds as $fileId) {
                    if (isset($fileIdToResourceId[$fileId])) {
                        $metaResource = new MetaResource();
                        $metaResource->meta_id = $metaId;
                        $metaResource->resource_id = $fileIdToResourceId[$fileId];

                        if (!$metaResource->save()) {
                            throw new \Exception('Failed to create MetaResource: ' . json_encode($metaResource->getErrors()));
                        }
                    }
                }
            }

            // --- Step 7: ID Remapping ---
            // Build replacement maps for both UUID-based and numeric-ID-based lookups.
            // verse.data uses numeric meta_id (e.g. "2290"), so we need originalId => newId.
            $metaIdReplacementMap = $metaIdMap; // originalUuid => new meta ID
            $resourceIdReplacementMap = $resourceIdMap; // originalUuid => new resource ID

            // Build numeric ID map: original meta numeric ID => new meta ID
            $metaNumericIdMap = [];
            foreach ($metasData as $metaInput) {
                if (isset($metaInput['id'], $metaIdMap[$metaInput['uuid']])) {
                    $metaNumericIdMap[(string) $metaInput['id']] = $metaIdMap[$metaInput['uuid']];
                }
            }
            // Build numeric ID map for resources
            $resourceNumericIdMap = [];
            foreach ($resourceFileMappings as $mapping) {
                if (isset($mapping['originalId'], $resourceIdMap[$mapping['originalUuid']])) {
                    $resourceNumericIdMap[(string) $mapping['originalId']] = $resourceIdMap[$mapping['originalUuid']];
                }
            }
            // Merge both UUID and numeric maps (use + to preserve string-numeric keys)
            $metaIdReplacementMap = $metaIdReplacementMap + $metaNumericIdMap;
            $resourceIdReplacementMap = $resourceIdReplacementMap + $resourceNumericIdMap;

            // Remap verse.data
            $verseDataJson = $verseData['data'] ?? null;
            if ($verseDataJson !== null) {
                $verseDataDecoded = is_string($verseDataJson) ? json_decode($verseDataJson, true) : $verseDataJson;
                if (is_array($verseDataDecoded)) {
                    $verseDataDecoded = IdRemapper::remap($verseDataDecoded, [
                        ['key' => 'meta_id', 'map' => $metaIdReplacementMap, 'numericOnly' => false],
                        ['key' => 'resource', 'map' => $resourceIdReplacementMap, 'numericOnly' => true],
                    ]);
                    $verseDataJson = $verseDataDecoded;
                }
            }

            // Remap each meta's data and events
            $remappedMetaData = [];
            foreach ($metaOriginalData as $metaId => $originalData) {
                $metaDataJson = $originalData['data'];
                $metaEventsJson = $originalData['events'];

                // Remap meta.data
                if ($metaDataJson !== null) {
                    $metaDataDecoded = is_string($metaDataJson) ? json_decode($metaDataJson, true) : $metaDataJson;
                    if (is_array($metaDataDecoded)) {
                        $metaDataDecoded = IdRemapper::remap($metaDataDecoded, [
                            ['key' => 'resource', 'map' => $resourceIdReplacementMap, 'numericOnly' => true],
                        ]);
                        $metaDataJson = $metaDataDecoded;
                    }
                }

                // Remap meta.events
                if ($metaEventsJson !== null) {
                    $metaEventsDecoded = is_string($metaEventsJson) ? json_decode($metaEventsJson, true) : $metaEventsJson;
                    if (is_array($metaEventsDecoded)) {
                        $metaEventsDecoded = IdRemapper::remap($metaEventsDecoded, [
                            ['key' => 'resource', 'map' => $resourceIdReplacementMap, 'numericOnly' => true],
                        ]);
                        $metaEventsJson = $metaEventsDecoded;
                    }
                }

                $remappedMetaData[$metaId] = [
                    'data' => $metaDataJson,
                    'events' => $metaEventsJson,
                ];
            }

            // --- Step 8: Update Verse.data and each Meta.data/events ---
            $verse->data = $verseDataJson;
            if (!$verse->save()) {
                throw new \Exception('Failed to update Verse data: ' . json_encode($verse->getErrors()));
            }

            foreach ($remappedMetaData as $metaId => $remapped) {
                $meta = Meta::findOne($metaId);
                if ($meta === null) {
                    throw new \Exception("Meta not found for update: {$metaId}");
                }
                $meta->data = $remapped['data'];
                $meta->events = $remapped['events'];

                if (!$meta->save()) {
                    throw new \Exception('Failed to update Meta data: ' . json_encode($meta->getErrors()));
                }
            }

            // --- Step 9: Commit transaction ---
            $transaction->commit();

            return [
                'verseId' => $verse->id,
                'metaIdMap' => $metaIdMap,
                'resourceIdMap' => $resourceIdMap,
                'fileIdMap' => $fileIdMap,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * 从导出的图片数据创建 File 记录
     *
     * 使用导出数据中的文件信息创建一条新的 File 数据库记录。
     * url、filename、key 为必填字段；md5、type、size 为可选字段（存在则设置）。
     * user_id 由 File 模型的 BlameableBehavior 自动处理。
     *
     * @param array $imageData 包含 url, filename, key, md5, type, size
     * @return File 新创建的 File 模型实例
     * @throws \Exception 如果 File 保存失败
     */
    private function createFileFromImageData(array $imageData): File
    {
        $file = new File();

        // 设置必填字段
        $file->url = $imageData['url'];
        $file->filename = $imageData['filename'];
        $file->key = $imageData['key'];

        // 设置可选字段（存在则设置）
        if (isset($imageData['md5'])) {
            $file->md5 = $imageData['md5'];
        }
        if (isset($imageData['type'])) {
            $file->type = $imageData['type'];
        }
        if (isset($imageData['size'])) {
            $file->size = $imageData['size'];
        }

        if (!$file->save()) {
            throw new \Exception('Failed to create File from image data: ' . json_encode($file->getErrors()));
        }

        return $file;
    }

    /**
     * 构建 verse 数据部分
     *
     * @param Verse $verse
     * @return array
     */
    private function buildVerseData(Verse $verse): array
    {
        $data = [
            'id' => $verse->id,
            'author_id' => $verse->author_id,
            'name' => $verse->name,
            'description' => $verse->description,
            'info' => $verse->info,
            'data' => $verse->data,
            'uuid' => $verse->uuid,
            'version' => Version::getCurrentVersionNumber(),
            'verseRelease' => null,
        ];

        // 关联 image（File 对象）
        $image = $verse->image;
        $data['image'] = $image ? $this->buildFileData($image) : null;

        // 关联 verseCode
        $verseCode = $verse->verseCode;
        $data['verseCode'] = $verseCode ? [
            'blockly' => $verseCode->blockly,
            'lua' => $verseCode->lua,
            'js' => $verseCode->js,
        ] : null;

        return $data;
    }

    /**
     * 构建 meta 数据部分
     *
     * @param Meta $meta
     * @return array
     */
    private function buildMetaData(Meta $meta): array
    {
        // 获取 meta 关联的 resources（通过 MetaResource）
        $resources = $meta->getResources();
        $resourcesData = [];
        foreach ($resources as $resource) {
            $resourcesData[] = $this->buildResourceData($resource);
        }

        $data = [
            'id' => $meta->id,
            'author_id' => $meta->author_id,
            'uuid' => $meta->uuid,
            'title' => $meta->title,
            'data' => $meta->data,
            'events' => $meta->events,
            'image_id' => $meta->image_id,
            'image' => $meta->image ? $this->buildFileData($meta->image) : null,
            'prefab' => $meta->prefab,
            'resources' => $resourcesData,
            'editable' => $meta->editable,
            'viewable' => $meta->viewable,
        ];

        // 关联 metaCode
        $metaCode = $meta->metaCode;
        $data['metaCode'] = $metaCode ? [
            'blockly' => $metaCode->blockly,
            'lua' => $metaCode->lua,
            'js' => $metaCode->js,
        ] : null;

        return $data;
    }

    /**
     * 构建 resource 数据部分
     *
     * @param Resource $resource
     * @return array
     */
    private function buildResourceData(Resource $resource): array
    {
        $file = $resource->file;
        $image = $resource->image;

        return [
            'id' => $resource->id,
            'uuid' => $resource->uuid,
            'name' => $resource->name,
            'type' => $resource->type,
            'info' => $resource->info,
            'created_at' => $resource->created_at,
            'file' => $file ? $this->buildFileData($file) : null,
            'image' => $image ? $this->buildFileData($image) : null,
        ];
    }

    /**
     * 构建 file 数据部分
     *
     * @param File $file
     * @return array
     */
    private function buildFileData(File $file): array
    {
        return [
            'id' => $file->id,
            'md5' => $file->md5,
            'type' => $file->type,
            'url' => $file->filterUrl,
            'filename' => $file->filename,
            'size' => $file->size,
            'key' => $file->key,
        ];
    }
}
