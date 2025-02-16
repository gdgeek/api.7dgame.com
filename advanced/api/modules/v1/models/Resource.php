<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;

use yii\caching\TagDependency;
use api\modules\v1\components\Validator\JsonValidator;
/**
 * This is the model class for table "resource".
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
            [['created_at'], 'safe'],
            [['info'], JsonValidator::class],
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
            $this->uuid = \Faker\Provider\Uuid::uuid();
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

    /**
     * Gets query for [[MetaResources]].
     *
     * @return \yii\db\ActiveQuery|MetaResourceQuery
     */
    public function getMetaResources()
    {
        return $this->hasMany(MetaResource::class, ['resource_id' => 'id']);
    }

    public function extraFields()
    {
        return [
            'file',
            'image',
            'metaResources',
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
