<?php

namespace api\modules\v1\models;

//use api\modules\v1\models\Cyber;
use api\modules\v1\models\File;
use api\modules\v1\models\User;
use Yii;
use api\modules\v1\models\MetaCode;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use OpenApi\Annotations as OA;

use api\modules\v1\components\Validator\JsonValidator;

/**
 * This is the model class for table "meta".
 *
 * @OA\Schema(
 *     schema="Meta",
 *     title="Meta 元数据",
 *     description="Meta 元数据模型",
 *     @OA\Property(property="id", type="integer", description="Meta ID", example=1),
 *     @OA\Property(property="uuid", type="string", description="Meta UUID", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="title", type="string", description="Meta 标题", example="My Meta"),
 *     @OA\Property(property="info", type="string", description="Meta 信息（JSON）", example="{}"),
 *     @OA\Property(property="data", type="string", description="Meta 数据（JSON）", example="{}"),
 *     @OA\Property(property="events", type="string", description="事件配置（JSON）", example="{}"),
 *     @OA\Property(property="image_id", type="integer", description="预览图片ID", example=10),
 *     @OA\Property(property="prefab", type="integer", description="是否为预制件（0=否，1=是）", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="创建时间"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新时间"),
 *     @OA\Property(property="image", ref="#/components/schemas/File", description="预览图片对象")
 * )
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property string|null $info
 * @property int|null $image_id
 * @property string|null $data
 * @property string|null $uuid
 *
 * @property User $author
 * @property File $image
 * @property User $updater
 * @property string|null $events
 * @property string|null $title
 *
 */
class Meta extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],

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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'updater_id', 'image_id', 'prefab'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info', 'data', 'events'], 'safe'],
            [['uuid', 'title'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    public function refreshResources()
    {
        $newResourceIds = array_unique(array_filter($this->getResourceIds()));
        $oldResourceIds = MetaResource::find()
            ->select('resource_id')
            ->where(['meta_id' => $this->id])
            ->column();
        $toAdd = array_diff($newResourceIds, $oldResourceIds);
        $toDelete = array_diff($oldResourceIds, $newResourceIds);

        if (!empty($toDelete)) {
            MetaResource::deleteAll(['meta_id' => $this->id, 'resource_id' => $toDelete]);
        }

        foreach ($toAdd as $resourceId) {
            $metaResource = new MetaResource();
            $metaResource->meta_id = $this->id;
            $metaResource->resource_id = $resourceId;
            $metaResource->save();
        }
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->refreshResources();
    }

    public function extraFields()
    {
        return [
            'image',
            'verseMetas',
            'author',
            'metaCode',
            'code' => 'metaCode',
            'lua',
            'js',
            'blockly',
        ];
    }
    public function fields()
    {

        return [
            'id',
            'image_id',
            'uuid',
            'events',
            'title',
            'prefab',
            'editable',
            'viewable',
            'resources',
            'data',
            'info',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'info' => 'Info',
            'image_id' => 'Image ID',
            'prefab' => 'Prefab',
            'data' => 'Data',
            'uuid' => 'Uuid',
        ];
    }
    public function getBlockly(): string
    {
        return $this->mateCode->blockly;
    }
    public function getLua(): string
    {
        return $this->mateCode->lua;
    }
    public function getJs(): string
    {
        return $this->mateCode->js;
    }

    public function getViewable()
    {
        return $this->viewable();
    }
    public function viewable()
    {
        if (!isset(Yii::$app->user->identity)) {
            return false;
        }
        $userid = Yii::$app->user->identity->id;
        if ($userid == $this->author_id) {
            return true;
        }

        return true;
    }
    public function getEditable()
    {
        return $this->editable();
    }

    public function editable()
    {
        if (!isset(Yii::$app->user->identity)) {

            return false;
        }
        $userid = Yii::$app->user->identity->id;
        if ($userid == $this->author_id) {
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
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Updater]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::class, ['id' => 'updater_id']);
    }
    public function getResourceIds()
    {
        $data = $this->data;
        // 如果 data 是 JSON 字符串，解码为数组
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        return \api\modules\v1\helper\Meta2Resources::Handle($data);
    }

    public function getResources()
    {
        return Resource::find()
            ->alias('r')
            ->innerJoin(['mr' => MetaResource::tableName()], 'mr.resource_id = r.id')
            ->where(['mr.meta_id' => $this->id])
            ->all();
    }



    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[MetaCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMetaCode()
    {
        return  $this->hasOne(MetaCode::class, ['meta_id' => 'id']);
    }

    /**
     * Gets query for [[VerseMeta]].
     *
     * @return \yii\db\ActiveQuery|VerseMetaQuery
     */
    public function getVerseMetas()
    {
        return $this->hasMany(VerseMeta::class, ['meta_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetaQuery(get_called_class());
    }
}
