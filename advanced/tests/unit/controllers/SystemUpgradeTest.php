<?php

namespace tests\unit\controllers;

use api\modules\v1\models\Code;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaCode;
use api\modules\v1\models\MetaResource;
use api\modules\v1\models\Property;
use api\modules\v1\models\Resource;
use api\modules\v1\models\Tags;
use api\modules\v1\models\User;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseCode;
use api\modules\v1\models\VerseMeta;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseTags;
use PHPUnit\Framework\TestCase;

/**
 * 系统升级功能测试
 */
class SystemUpgradeTest extends TestCase
{
    private ?User $testUser = null;
    private array $createdIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createdIds = [];
        $this->testUser = $this->getOrCreateTestUser();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function getOrCreateTestUser(): User
    {
        // 使用已存在的用户或创建新用户
        $user = User::find()->where(['like', 'username', 'upgrade_test_'])->one();
        if ($user) {
            return $user;
        }

        $user = new User();
        $user->detachBehaviors();
        $user->username = 'upgrade_test_user@example.com';
        $user->email = 'upgrade_test_user@example.com';
        $user->setPassword('Test123!@#');
        $user->generateAuthKey();
        $user->status = 10;
        $user->created_at = time();
        $user->updated_at = time();
        $user->save(false);
        return $user;
    }

    private function cleanup(): void
    {
        foreach ($this->createdIds['verse_properties'] ?? [] as $id) {
            VerseProperty::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['verse_tags'] ?? [] as $id) {
            VerseTags::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['verse_metas'] ?? [] as $id) {
            VerseMeta::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['meta_resources'] ?? [] as $id) {
            MetaResource::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['verse_codes'] ?? [] as $id) {
            VerseCode::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['meta_codes'] ?? [] as $id) {
            MetaCode::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['codes'] ?? [] as $id) {
            Code::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['verses'] ?? [] as $id) {
            Verse::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['metas'] ?? [] as $id) {
            Meta::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['resources'] ?? [] as $id) {
            Resource::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['files'] ?? [] as $id) {
            \api\modules\v1\models\File::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['properties'] ?? [] as $id) {
            Property::deleteAll(['id' => $id]);
        }
        foreach ($this->createdIds['tags'] ?? [] as $id) {
            Tags::deleteAll(['id' => $id]);
        }
    }

    private function createMeta(array $attrs = []): Meta
    {
        $meta = new Meta();
        $meta->detachBehaviors();
        $meta->author_id = $this->testUser->id;
        $meta->updater_id = $this->testUser->id;
        $meta->created_at = date('Y-m-d H:i:s');
        $meta->updated_at = date('Y-m-d H:i:s');
        foreach ($attrs as $k => $v) {
            // 将数组字段转换为 JSON 字符串
            if (in_array($k, ['data', 'info', 'events']) && is_array($v)) {
                $meta->$k = json_encode($v);
            } else {
                $meta->$k = $v;
            }
        }
        $meta->save(false);
        $this->createdIds['metas'][] = $meta->id;
        return $meta;
    }

    private function createVerse(array $attrs = []): Verse
    {
        $verse = new Verse();
        $verse->detachBehaviors();
        $verse->author_id = $this->testUser->id;
        $verse->updater_id = $this->testUser->id;
        $verse->name = $attrs['name'] ?? 'Test Verse ' . uniqid();
        $verse->created_at = date('Y-m-d H:i:s');
        foreach ($attrs as $k => $v) {
            // 将数组字段转换为 JSON 字符串
            if (in_array($k, ['data', 'info']) && is_array($v)) {
                $verse->$k = json_encode($v);
            } else {
                $verse->$k = $v;
            }
        }
        $verse->save(false);
        $this->createdIds['verses'][] = $verse->id;
        return $verse;
    }

    private function createFile(): \api\modules\v1\models\File
    {
        $file = new \api\modules\v1\models\File();
        $file->detachBehaviors();
        $file->user_id = $this->testUser->id;
        $file->md5 = md5(uniqid());
        $file->created_at = date('Y-m-d H:i:s');
        $file->save(false);
        $this->createdIds['files'][] = $file->id;
        return $file;
    }

    private function createResource(int $fileId): Resource
    {
        $resource = new Resource();
        $resource->detachBehaviors();
        $resource->author_id = $this->testUser->id;
        $resource->name = 'Test Resource ' . uniqid();
        $resource->type = 'polygen';
        $resource->file_id = $fileId;
        $resource->uuid = \common\components\UuidHelper::uuid();
        $resource->save(false);
        $this->createdIds['resources'][] = $resource->id;
        return $resource;
    }

    // ==================== Meta UUID 测试 ====================

    public function testMetaUuidGenerationWhenEmpty(): void
    {
        $meta = $this->createMeta(['uuid' => null]);
        $this->assertNull($meta->uuid);

        if (empty($meta->uuid)) {
            $meta->uuid = \common\components\UuidHelper::uuid();
            $meta->save(false);
        }

        $this->assertNotNull($meta->uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $meta->uuid);
    }

    public function testMetaUuidNotOverwrittenWhenExists(): void
    {
        $existingUuid = \common\components\UuidHelper::uuid();
        $meta = $this->createMeta(['uuid' => $existingUuid]);

        if (empty($meta->uuid)) {
            $meta->uuid = \common\components\UuidHelper::uuid();
            $meta->save(false);
        }

        $this->assertEquals($existingUuid, $meta->uuid);
    }

    // ==================== Verse UUID 测试 ====================

    public function testVerseUuidGenerationWhenEmpty(): void
    {
        $verse = $this->createVerse(['uuid' => null]);
        $this->assertNull($verse->uuid);

        if (empty($verse->uuid)) {
            $verse->uuid = \common\components\UuidHelper::uuid();
            $verse->save(false);
        }

        $this->assertNotNull($verse->uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $verse->uuid);
    }

    public function testVerseUuidNotOverwrittenWhenExists(): void
    {
        $existingUuid = \common\components\UuidHelper::uuid();
        $verse = $this->createVerse(['uuid' => $existingUuid]);

        if (empty($verse->uuid)) {
            $verse->uuid = \common\components\UuidHelper::uuid();
            $verse->save(false);
        }

        $this->assertEquals($existingUuid, $verse->uuid);
    }

    // ==================== Meta refreshResources 测试 ====================

    public function testMetaRefreshResourcesAddsNewResources(): void
    {
        $file = $this->createFile();
        $resource = $this->createResource($file->id);

        $meta = $this->createMeta([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['type' => 'polygen', 'parameters' => ['resource' => $resource->id]]
        ]);

        $meta->refreshResources();

        $mr = MetaResource::findOne(['meta_id' => $meta->id, 'resource_id' => $resource->id]);
        $this->assertNotNull($mr);
        $this->createdIds['meta_resources'][] = $mr->id;
    }

    public function testMetaRefreshResourcesRemovesOldResources(): void
    {
        $file1 = $this->createFile();
        $resource1 = $this->createResource($file1->id);
        $file2 = $this->createFile();
        $resource2 = $this->createResource($file2->id);

        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid(), 'data' => []]);

        // 手动创建旧关联
        $mr = new MetaResource();
        $mr->meta_id = $meta->id;
        $mr->resource_id = $resource1->id;
        $mr->save(false);

        // 更新 data 只包含 resource2
        $meta->data = json_encode(['type' => 'polygen', 'parameters' => ['resource' => $resource2->id]]);
        $meta->save(false);
        $meta->refreshResources();

        $this->assertNull(MetaResource::findOne(['meta_id' => $meta->id, 'resource_id' => $resource1->id]));
        $newMr = MetaResource::findOne(['meta_id' => $meta->id, 'resource_id' => $resource2->id]);
        $this->assertNotNull($newMr);
        $this->createdIds['meta_resources'][] = $newMr->id;
    }

    public function testMetaRefreshResourcesIsIdempotent(): void
    {
        $file = $this->createFile();
        $resource = $this->createResource($file->id);

        $meta = $this->createMeta([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['type' => 'polygen', 'parameters' => ['resource' => $resource->id]]
        ]);

        $meta->refreshResources();
        $meta->refreshResources();
        $meta->refreshResources();

        $count = MetaResource::find()->where(['meta_id' => $meta->id])->count();
        $this->assertEquals(1, $count);

        $mr = MetaResource::findOne(['meta_id' => $meta->id]);
        $this->createdIds['meta_resources'][] = $mr->id;
    }

    // ==================== Verse refreshMetas 测试 ====================

    public function testVerseRefreshMetasAddsNewMetas(): void
    {
        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);

        $verse = $this->createVerse([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['children' => ['modules' => [['parameters' => ['meta_id' => $meta->id]]]]]
        ]);

        $verse->refreshMetas();

        $vm = VerseMeta::findOne(['verse_id' => $verse->id, 'meta_id' => $meta->id]);
        $this->assertNotNull($vm);
        $this->createdIds['verse_metas'][] = $vm->id;
    }

    public function testVerseRefreshMetasRemovesOldMetas(): void
    {
        $meta1 = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);
        $meta2 = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);

        $verse = $this->createVerse(['uuid' => \common\components\UuidHelper::uuid(), 'data' => []]);

        // 手动创建旧关联
        $vm = new VerseMeta();
        $vm->verse_id = $verse->id;
        $vm->meta_id = $meta1->id;
        $vm->save(false);

        // 更新 data 只包含 meta2
        $verse->data = json_encode(['children' => ['modules' => [['parameters' => ['meta_id' => $meta2->id]]]]]);
        $verse->save(false);
        $verse->refreshMetas();

        $this->assertNull(VerseMeta::findOne(['verse_id' => $verse->id, 'meta_id' => $meta1->id]));
        $newVm = VerseMeta::findOne(['verse_id' => $verse->id, 'meta_id' => $meta2->id]);
        $this->assertNotNull($newVm);
        $this->createdIds['verse_metas'][] = $newVm->id;
    }

    public function testVerseRefreshMetasIsIdempotent(): void
    {
        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);

        $verse = $this->createVerse([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['children' => ['modules' => [['parameters' => ['meta_id' => $meta->id]]]]]
        ]);

        $verse->refreshMetas();
        $verse->refreshMetas();
        $verse->refreshMetas();

        $count = VerseMeta::find()->where(['verse_id' => $verse->id])->count();
        $this->assertEquals(1, $count);

        $vm = VerseMeta::findOne(['verse_id' => $verse->id]);
        $this->createdIds['verse_metas'][] = $vm->id;
    }

    public function testVerseRefreshMetasWithMultipleMetas(): void
    {
        $metas = [];
        for ($i = 0; $i < 3; $i++) {
            $metas[] = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);
        }

        $modules = array_map(fn($m) => ['parameters' => ['meta_id' => $m->id]], $metas);
        $verse = $this->createVerse([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['children' => ['modules' => $modules]]
        ]);

        $verse->refreshMetas();

        $count = VerseMeta::find()->where(['verse_id' => $verse->id])->count();
        $this->assertEquals(3, $count);

        foreach ($metas as $meta) {
            $vm = VerseMeta::findOne(['verse_id' => $verse->id, 'meta_id' => $meta->id]);
            $this->assertNotNull($vm);
            $this->createdIds['verse_metas'][] = $vm->id;
        }
    }

    // ==================== Code 迁移测试 ====================

    public function testCodeMigrationToMetaCode(): void
    {
        $code = new Code();
        $code->lua = 'print("Hello Lua")';
        $code->js = 'console.log("Hello JS")';
        $code->save(false);
        $this->createdIds['codes'][] = $code->id;

        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);

        $metaCode = new MetaCode();
        $metaCode->meta_id = $meta->id;
        $metaCode->code_id = $code->id;
        $metaCode->lua = null;
        $metaCode->js = null;
        $metaCode->save(false);
        $this->createdIds['meta_codes'][] = $metaCode->id;

        // 模拟升级逻辑
        if ($metaCode->code) {
            $changed = false;
            if (!empty($metaCode->code->lua) && $metaCode->lua !== $metaCode->code->lua) {
                $metaCode->lua = $metaCode->code->lua;
                $changed = true;
            }
            if (!empty($metaCode->code->js) && $metaCode->js !== $metaCode->code->js) {
                $metaCode->js = $metaCode->code->js;
                $changed = true;
            }
            if ($changed) $metaCode->save(false);
        }

        $metaCode->refresh();
        $this->assertEquals('print("Hello Lua")', $metaCode->lua);
        $this->assertEquals('console.log("Hello JS")', $metaCode->js);
    }

    public function testCodeMigrationToVerseCode(): void
    {
        $code = new Code();
        $code->lua = 'print("Verse Lua")';
        $code->js = 'console.log("Verse JS")';
        $code->save(false);
        $this->createdIds['codes'][] = $code->id;

        $verse = $this->createVerse(['uuid' => \common\components\UuidHelper::uuid()]);

        $verseCode = new VerseCode();
        $verseCode->verse_id = $verse->id;
        $verseCode->code_id = $code->id;
        $verseCode->lua = null;
        $verseCode->js = null;
        $verseCode->save(false);
        $this->createdIds['verse_codes'][] = $verseCode->id;

        // 模拟升级逻辑
        if ($verseCode->code) {
            $changed = false;
            if (!empty($verseCode->code->lua) && $verseCode->lua !== $verseCode->code->lua) {
                $verseCode->lua = $verseCode->code->lua;
                $changed = true;
            }
            if (!empty($verseCode->code->js) && $verseCode->js !== $verseCode->code->js) {
                $verseCode->js = $verseCode->code->js;
                $changed = true;
            }
            if ($changed) $verseCode->save(false);
        }

        $verseCode->refresh();
        $this->assertEquals('print("Verse Lua")', $verseCode->lua);
        $this->assertEquals('console.log("Verse JS")', $verseCode->js);
    }

    // ==================== Tag 迁移到 Property 测试 ====================

    public function testTagMigrationCreatesProperty(): void
    {
        $uniqueKey = 'test_tag_' . uniqid();

        $tag = new Tags();
        $tag->name = 'Test Tag';
        $tag->key = $uniqueKey;
        // 不设置 type 字段，让数据库使用默认值
        $tag->save(false);
        $this->createdIds['tags'][] = $tag->id;

        $property = Property::findOne(['key' => $uniqueKey]);
        if (!$property) {
            $property = new Property();
            $property->key = $uniqueKey;
            $property->info = $tag->name;
            $property->save(false);
        }
        $this->createdIds['properties'][] = $property->id;

        $this->assertNotNull($property->id);
        $this->assertEquals($uniqueKey, $property->key);
        $this->assertEquals('Test Tag', $property->info);
    }

    public function testTagMigrationCreatesVerseProperty(): void
    {
        $uniqueKey = 'test_tag_vp_' . uniqid();

        $tag = new Tags();
        $tag->name = 'Test Tag VP';
        $tag->key = $uniqueKey;
        // 不设置 type 字段，让数据库使用默认值
        $tag->save(false);
        $this->createdIds['tags'][] = $tag->id;

        $verse = $this->createVerse(['uuid' => \common\components\UuidHelper::uuid()]);

        // 使用正确的列名 tags_id（数据库中的实际列名）
        $verseTags = new VerseTags();
        $verseTags->verse_id = $verse->id;
        $verseTags->tags_id = $tag->id;
        $verseTags->save(false);
        $this->createdIds['verse_tags'][] = $verseTags->id;

        // 模拟迁移
        $property = Property::findOne(['key' => $uniqueKey]);
        if (!$property) {
            $property = new Property();
            $property->key = $uniqueKey;
            $property->info = $tag->name;
            $property->save(false);
        }
        $this->createdIds['properties'][] = $property->id;

        $existingVp = VerseProperty::findOne(['verse_id' => $verse->id, 'property_id' => $property->id]);
        if (!$existingVp) {
            $vp = new VerseProperty();
            $vp->verse_id = $verse->id;
            $vp->property_id = $property->id;
            $vp->save(false);
            $this->createdIds['verse_properties'][] = $vp->id;
        }

        $verseProperty = VerseProperty::findOne(['verse_id' => $verse->id, 'property_id' => $property->id]);
        $this->assertNotNull($verseProperty);
    }

    // ==================== 边界情况测试 ====================

    public function testMetaRefreshResourcesWithEmptyData(): void
    {
        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid(), 'data' => null]);
        $meta->refreshResources();

        $count = MetaResource::find()->where(['meta_id' => $meta->id])->count();
        $this->assertEquals(0, $count);
    }

    public function testVerseRefreshMetasWithEmptyData(): void
    {
        $verse = $this->createVerse(['uuid' => \common\components\UuidHelper::uuid(), 'data' => null]);
        $verse->refreshMetas();

        $count = VerseMeta::find()->where(['verse_id' => $verse->id])->count();
        $this->assertEquals(0, $count);
    }

    public function testVerseRefreshMetasWithIncompleteData(): void
    {
        $verse = $this->createVerse([
            'uuid' => \common\components\UuidHelper::uuid(),
            'data' => ['children' => []] // 缺少 modules
        ]);
        $verse->refreshMetas();

        $count = VerseMeta::find()->where(['verse_id' => $verse->id])->count();
        $this->assertEquals(0, $count);
    }

    public function testMetaCodeUpgradeWithoutCode(): void
    {
        $meta = $this->createMeta(['uuid' => \common\components\UuidHelper::uuid()]);

        $metaCode = new MetaCode();
        $metaCode->meta_id = $meta->id;
        $metaCode->code_id = null;
        $metaCode->lua = 'existing lua';
        $metaCode->js = 'existing js';
        $metaCode->save(false);
        $this->createdIds['meta_codes'][] = $metaCode->id;

        // 模拟升级逻辑 - code 为 null 时不应执行
        if ($metaCode->code) {
            $this->fail('不应进入 code 迁移逻辑');
        }

        $metaCode->refresh();
        $this->assertEquals('existing lua', $metaCode->lua);
        $this->assertEquals('existing js', $metaCode->js);
    }

    public function testVerseCodeUpgradeWithoutCode(): void
    {
        $verse = $this->createVerse(['uuid' => \common\components\UuidHelper::uuid()]);

        $verseCode = new VerseCode();
        $verseCode->verse_id = $verse->id;
        $verseCode->code_id = null;
        $verseCode->lua = 'existing verse lua';
        $verseCode->js = 'existing verse js';
        $verseCode->save(false);
        $this->createdIds['verse_codes'][] = $verseCode->id;

        // 模拟升级逻辑 - code 为 null 时不应执行
        if ($verseCode->code) {
            $this->fail('不应进入 code 迁移逻辑');
        }

        $verseCode->refresh();
        $this->assertEquals('existing verse lua', $verseCode->lua);
        $this->assertEquals('existing verse js', $verseCode->js);
    }
}
