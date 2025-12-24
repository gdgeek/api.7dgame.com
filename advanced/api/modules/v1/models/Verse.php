<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
use api\modules\v1\models\User;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseTags;
use api\modules\v1\models\VerseCode;
use yii\db\ActiveQuery;
use yii\db\Query;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
* This is the model class for table "verse".
*
* @property int $id
* @property int $author_id
* @property int|null $updater_id
* @property string $created_at
* @property string $updated_at
* @property string $name
* @property string|null $info
* @property int|null $image_id
* @property string|null $data
* @property string|null $description
*
* @property Manager[] $managers
* @property Meta[] $metas
* @property User $author
* @property File $image_id0
* @property User $updater

*/
class Verse extends \yii\db\ActiveRecord
{
    private const GROUP_EDITABLE_PREFETCH_THRESHOLD = 20;

    private const GROUP_EDITABLE_CACHE_DURATION = 300;
    private const GROUP_VERSE_REV_CACHE_KEY = 'verse_group_verse_rev';
    private const USER_GROUP_REV_CACHE_KEY_PREFIX = 'verse_user_group_rev_';
    private const USER_EDITABLE_VERSE_SET_CACHE_KEY_PREFIX = 'verse_group_editable_verse_ids_';

    /**
     * 请求内缓存：当前用户通过 group 成员身份可编辑的 verseId 集合
     * 结构：[
     *   userId => [ verseId => true, ... ],
     * ]
     *
     * @var array
     */
    private static $groupEditableVerseIdSetByUser = [];

    /**
     * 请求内缓存：按 (userId, verseId) 记忆化 exists 结果
     * 结构：[ userId => [ verseId => bool, ... ] ]
     *
     * @var array
     */
    private static $groupEditableMemoByUser = [];

    /**
     * 请求内计数：同一用户在一次请求里检查 editable 的次数
     *
     * @var array
     */
    private static $groupEditableCheckCountByUser = [];

    public static function bumpGroupVerseRevision(): void
    {
        $cache = Yii::$app->cache ?? null;
        if (!$cache) {
            return;
        }

        $current = $cache->get(self::GROUP_VERSE_REV_CACHE_KEY);
        $next = is_numeric($current) ? ((int) $current + 1) : 2;
        $cache->set(self::GROUP_VERSE_REV_CACHE_KEY, $next, 0);
    }

    public static function bumpUserGroupRevision(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $cache = Yii::$app->cache ?? null;
        if (!$cache) {
            return;
        }

        $key = self::USER_GROUP_REV_CACHE_KEY_PREFIX . $userId;
        $current = $cache->get($key);
        $next = is_numeric($current) ? ((int) $current + 1) : 2;
        $cache->set($key, $next, 0);
    }

    private static function getGroupVerseRevision(): int
    {
        $cache = Yii::$app->cache ?? null;
        if (!$cache) {
            return 1;
        }

        $value = $cache->get(self::GROUP_VERSE_REV_CACHE_KEY);
        return is_numeric($value) ? (int) $value : 1;
    }

    private static function getUserGroupRevision(int $userId): int
    {
        $cache = Yii::$app->cache ?? null;
        if (!$cache) {
            return 1;
        }

        $value = $cache->get(self::USER_GROUP_REV_CACHE_KEY_PREFIX . $userId);
        return is_numeric($value) ? (int) $value : 1;
    }

    private static function getUserEditableVerseSetCacheKey(int $userId): string
    {
        $groupVerseRev = self::getGroupVerseRevision();
        $userGroupRev = self::getUserGroupRevision($userId);

        return self::USER_EDITABLE_VERSE_SET_CACHE_KEY_PREFIX . $userId . '_gr' . $groupVerseRev . '_ur' . $userGroupRev;
    }

    /**
     * @return array|null 返回 verseIdSet（[id=>true]）或 null（缓存未命中）
     */
    private static function getCachedGroupEditableVerseIdSet(int $userId): ?array
    {
        $cache = Yii::$app->cache ?? null;
        if (!$cache) {
            return null;
        }

        $key = self::getUserEditableVerseSetCacheKey($userId);
        $cached = $cache->get($key);
        if ($cached === false || !is_array($cached)) {
            return null;
        }

        return $cached;
    }

    /**
     * @return array 返回 verseIdSet（[id=>true]）
     */
    private static function getOrBuildGroupEditableVerseIdSet(int $userId): array
    {
        $cached = self::getCachedGroupEditableVerseIdSet($userId);
        if ($cached !== null) {
            return $cached;
        }

        $verseIds = (new Query())
            ->select('DISTINCT gv.verse_id')
            ->from(['gv' => 'group_verse'])
            ->innerJoin(['gu' => 'group_user'], 'gu.group_id = gv.group_id')
            ->where(['gu.user_id' => $userId])
            ->column();

        $set = $verseIds
            ? array_fill_keys(array_map('intval', $verseIds), true)
            : [];

        $cache = Yii::$app->cache ?? null;
        if ($cache) {
            $cache->set(
                self::getUserEditableVerseSetCacheKey($userId),
                $set,
                self::GROUP_EDITABLE_CACHE_DURATION
            );
        }

        return $set;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'author_id',
                'updatedByAttribute' => 'updater_id',
            ],
        ];
    }

    /** 
     * Gets query for [[Managers]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */
    public function getManagers(): ActiveQuery
    {
        return $this->hasMany(Manager::className(), ['verse_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['author_id', 'updater_id', 'image_id'], 'integer'],
            [['created_at', 'updated_at', 'info', 'data'], 'safe'],
            [['name', 'uuid', 'description'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['updater_id']);
        unset($fields['image_id']);
        unset($fields['updated_at']);
        unset($fields['script']);


        $fields['description'] = function () {
            return $this->description;
        };
        $fields['editable'] = function () {
            return $this->editable;
        };
        $fields['viewable'] = function () {
            return $this->viewable;
        };

        $fields['info'] = function () {

            return $this->info;
        };
        $fields['data'] = function () {
            return $this->data;
        };

        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'author_id' => Yii::t('app', 'Author ID'),
            'updater_id' => Yii::t('app', 'Updater ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Name'),
            'info' => Yii::t('app', 'Info'),
            'data' => Yii::t('app', 'Data'),
            'image_id' => Yii::t('app', 'Image ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'description' => Yii::t('app', 'Description'),
        ];
    }
    /**
     * Gets query for [[Version]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVersion()
    {
        return $this->hasOne(Version::className(), ['id' => 'version_id'])
            ->viaTable('verse_version', ['verse_id' => 'id']);
    }

    /**
     * Gets query for [[VerseCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseCode()
    {

        $quest = $this->hasOne(VerseCode::className(), ['verse_id' => 'id']);
        $code = $quest->one();
        if ($code == null) {

            $code = new VerseCode();
            $code->verse_id = $this->id;
            $code->save();
        }

        return $quest;
    }


    public function getResources(): array
    {
        return Resource::find()
            ->alias('r')
            ->select('r.*')
            ->distinct()
            ->innerJoin(['mr' => MetaResource::tableName()], 'mr.resource_id = r.id')
            ->innerJoin(['vm' => VerseMeta::tableName()], 'vm.meta_id = mr.meta_id')
            ->where(['vm.verse_id' => $this->id])
            ->all();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $newMetaIds = array_unique(array_filter($this->getMetaIds()));
        $oldMetaIds = VerseMeta::find()
            ->select('meta_id')
            ->where(['verse_id' => $this->id])
            ->column();

        $toAdd = array_diff($newMetaIds, $oldMetaIds);
        $toDelete = array_diff($oldMetaIds, $newMetaIds);

        if (!empty($toDelete)) {
            VerseMeta::deleteAll(['verse_id' => $this->id, 'meta_id' => $toDelete]);
        }
       // throw new \Exception('toAdd: ' . json_encode($toAdd));
        foreach ($toAdd as $metaId) {
            $verseMeta = new VerseMeta();
            $verseMeta->verse_id = $this->id;
            $verseMeta->meta_id = $metaId;
            $verseMeta->save();
        }
    }
    
    public function afterFind()
    {
        parent::afterFind();
        VerseVersion::upgrade($this);
        
    }

    public function extraFields()
    {

        return [
            'metas',
            'image',
            'author',
            'public',
            'description',
            'resources',
            'verseCode',
            'verseTags',
            'tags',
            'version',
        ];



    }
   

    public function getMetaIds()
    {
        $data = $this->data;
        if (!isset($data['children']) || !isset($data['children']['modules'])) {
            return [];
        }
        return array_map(function ($item) {
            return $item['parameters']['meta_id'] ?? null;
        }, is_array($data['children']['modules']) ? $data['children']['modules'] : []);
    }


    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */


    public function getMetas(): ActiveQuery
    {
        
        return $this->hasMany(Meta::className(), ['id' => 'meta_id'])
            ->viaTable('verse_meta', ['verse_id' => 'id']);
    }


    /**
     * Gets query for [[VerseTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseTags()
    {
        return $this->hasMany(VerseTags::className(), ['verse_id' => 'id']);
    }

    /**
     * Gets tag names associated with this verse
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        // 方法一：如果 VerseTags 有关联到 Tag 模型
        return $this->hasMany(Tags::className(), ['id' => 'tags_id'])
            ->viaTable('verse_tags', ['verse_id' => 'id']);
    }


    public function getPublic()
    {
        $tag = $this->getProperties()->andWhere(['key' => 'public'])->one();
        if ($tag) {
            return true;
        }
        return false;
    }

    /**
     * Gets query for [[VerseProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseProperties()
    {
        return $this->hasMany(VerseProperty::className(), ['verse_id' => 'id']);
    }
    public function getProperties()
    {
        return $this->hasMany(Property::className(), ['id' => 'property_id'])
            ->viaTable('verse_property', ['verse_id' => 'id']);
    }

    public function getEditable()
    {
        return $this->editable();
    }

    public function editable()
    {

        if (
            isset(Yii::$app->user->identity)
            && (int) Yii::$app->user->id === (int) $this->author_id
        ) {
            return true;
        }

        $userId = (int) Yii::$app->user->id;
        if ($userId <= 0) {
            return false;
        }

        $verseId = (int) $this->id;

        // 先尝试跨请求缓存（命中后写入请求内 set）
        if (!isset(self::$groupEditableVerseIdSetByUser[$userId])) {
            $cachedSet = self::getCachedGroupEditableVerseIdSet($userId);
            if ($cachedSet !== null) {
                self::$groupEditableVerseIdSetByUser[$userId] = $cachedSet;
                return isset(self::$groupEditableVerseIdSetByUser[$userId][$verseId]);
            }
        }

        // 如果已经预取过全集合，O(1) 判断
        if (isset(self::$groupEditableVerseIdSetByUser[$userId])) {

            return isset(self::$groupEditableVerseIdSetByUser[$userId][$verseId]);
        }

        // 先走记忆化：避免同一请求内重复判断同一个 verse
        if (isset(self::$groupEditableMemoByUser[$userId][$verseId])) {

            return (bool) self::$groupEditableMemoByUser[$userId][$verseId];
        }

        // 自适应策略：小量调用用 EXISTS（避免一次性拉全量 verse_id）；大量调用（列表）再预取
        $count = (int) (self::$groupEditableCheckCountByUser[$userId] ?? 0) + 1;
        self::$groupEditableCheckCountByUser[$userId] = $count;

        if ($count >= self::GROUP_EDITABLE_PREFETCH_THRESHOLD) {
            self::$groupEditableVerseIdSetByUser[$userId] = self::getOrBuildGroupEditableVerseIdSet($userId);

            return isset(self::$groupEditableVerseIdSetByUser[$userId][$verseId]);
        }

        // EXISTS 单条判断：
        // 只要该 verse 在任意一个 group 里，并且当前用户属于该 group，则 editable=true
        $exists = (new Query())
            ->from(['gv' => 'group_verse'])
            ->innerJoin(['gu' => 'group_user'], 'gu.group_id = gv.group_id')
            ->where(['gu.user_id' => $userId, 'gv.verse_id' => $verseId])
            ->exists();

        self::$groupEditableMemoByUser[$userId][$verseId] = (bool) $exists;

        return (bool) $exists;
    }
    public function getViewable()
    {
        return $this->viewable();
    }
    public function viewable()
    {
        if ($this->public || $this->editable) {
            return true;
        }
        return false;
    }
    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|FileQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }

    /**
     * Gets query for [[Updater]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * {@inheritdoc}
     * @return VerseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseQuery(get_called_class());
    }

}
