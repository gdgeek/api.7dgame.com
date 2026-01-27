<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use OpenApi\Annotations as OA;

use yii\caching\TagDependency;

/**
 * This is the model class for table "resource".
 *
 * @OA\Schema(
 *     schema="Resource",
 *     title="资源",
 *     description="资源模型",
 *     @OA\Property(property="id", type="integer", description="资源ID", example=1),
 *     @OA\Property(property="name", type="string", description="资源名称", example="My Resource"),
 *     @OA\Property(property="type", type="string", description="资源类型", example="image"),
 *     @OA\Property(property="uuid", type="string", description="资源UUID", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="info", type="string", description="资源信息（JSON）", example="{}"),
 *     @OA\Property(property="image_id", type="integer", description="预览图片ID", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="创建时间"),
 *     @OA\Property(property="file", ref="#/components/schemas/File", description="文件对象"),
 *     @OA\Property(property="image", ref="#/components/schemas/File", description="预览图片对象")
 * )
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $author_id
 * @property string $created_at
 * @property int $file_id
 * @property int|null $image_id
 * @property string|null $info
 * @property int|null $updater_id
 * @property string|null $uuid
 *
 * @property User $author
 * @property File $file
 * @property File $image
 * @property User $updater
 */
class Resource extends \yii\db\ActiveRecord

{


    public function  afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(Yii::$app->cache, 'resource_cache');
    }
    public function behaviors()
    {
        return [
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
        return 'resource';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type', 'file_id'], 'required'],
            [['author_id', 'updater_id', 'file_id', 'image_id'], 'integer'],
            [['created_at','info'], 'safe'],
            [['name', 'type', 'uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updater_id' => 'id']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'created_at' => 'Created At',
            'file_id' => 'File ID',
            'image_id' => 'Image ID',
            'info' => 'Info',
            'uuid' => 'Uuid',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::class, ['id' => 'updater_id']);
    }
    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
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
     * {@inheritdoc}
     * @return ResourceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResourceQuery(get_called_class());
    }
    public function afterFind()
    {

        parent::afterFind();
        if (empty($this->uuid)) {
            $this->uuid = \common\components\UuidHelper::uuid();
            $this->save();
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();
        $file = $this->file;
        if ($file) {
            $file->delete();
        }
        $image = $this->image;
        if ($image) {
            $image->delete();
        }
    }

  

    public function extraFields()
    {
        return [
            'file',
            'image',
            'author',
        ];
    }
    /*
    public function getSample()
    {
    return ['id' => $this->id, 'info' => $this->info, 'md5' => $this->file->md5, 'uuid' => $this->uuid, 'fileData' => $this->file, 'file' => $this->file->url, 'type' => $this->type];
    }*/
    public function fields()
    {
      

        return [
            'id',
            'info'=>function($model){
                if(!is_string($model->info) && !is_null($model->info)){
                    return json_encode($model->info);
                }
                return $model->info;
            },
            'name',
            'uuid',
            'type',
            'image_id',
            'image' => function ($model) {
                return $this->image;
            },
            'created_at', 'file' => function ($model) {
                return $this->file;
            },

        ];
    }

}
