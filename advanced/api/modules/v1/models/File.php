<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\AttributesBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use yii\caching\TagDependency;
use OpenApi\Annotations as OA;

/**
 * This is the model class for table "file".
 *
 * @OA\Schema(
 *     schema="File",
 *     title="文件",
 *     description="文件模型",
 *     @OA\Property(property="id", type="integer", description="文件ID", example=1),
 *     @OA\Property(property="md5", type="string", description="文件MD5值", example="5d41402abc4b2a76b9719d911017c592"),
 *     @OA\Property(property="type", type="string", description="文件MIME类型", example="image/jpeg"),
 *     @OA\Property(property="url", type="string", description="文件URL", example="https://example.com/files/image.jpg"),
 *     @OA\Property(property="filename", type="string", description="文件名", example="image.jpg"),
 *     @OA\Property(property="size", type="integer", description="文件大小（字节）", example=1048576),
 *     @OA\Property(property="key", type="string", description="存储键", example="uploads/2024/file123")
 * )
 *
 * @property int $id
 * @property string $md5
 * @property string|null $type
 * @property string|null $url
 * @property int $user_id
 * @property string $created_at
 * @property string|null $filename
 *
 * @property User $user
 * @property Picture[] $pictures
 * @property Polygen[] $polygens
 * @property Polygen[] $polygens0
 * @property Video[] $videos
 */
class File extends ActiveRecord

{
/*
    public function  afterSave($insert, $changedAttributes)
    {
      
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(Yii::$app->cache, 'file_cache');
    }*/
    private $header = null;
    private function getFileHeader()
    {
        if (isset($this->filterUrl) && $this->header == null) {
            $this->header = get_headers($this->filterUrl, true);
        }
        return $this->header;
    }
    public function getFileSize()
    {
        $header = $this->getFileHeader();
        if (isset($header)) {
            $filesize = round(ArrayHelper::getValue($header, 'Content-Length', 0), 2);
            return $filesize;
        }
        return null;
    }
    public function getFileETag()
    {
        $header = $this->getFileHeader();
        if (isset($header)) {
            return json_decode(ArrayHelper::getValue($header, 'ETag'));
        }
        return null;
    }
    public function getFileType()
    {
        $header = $this->getFileHeader();
        if (isset($header)) {
            return ArrayHelper::getValue($header, 'Content-Type', 'application/octet-stream');
        }
        return 'application/octet-stream';

    }
    public function getFilterUrl()
    {
        if (preg_match('/^http[s]?:\/\/(\d+.\d+.\d+.\d+)[:]?\d+[\/]?/',
            Yii::$app->request->hostInfo, $matches)) {
            return str_replace('[ip]', $matches[1], $this->url);
        }
        return $this->url;
    }
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['updater_id']);
        unset($fields['user_id']);
        unset($fields['created_at']);
        $fields['url'] = function () {
            return $this->filterUrl;
        };
        return $fields;
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
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
            [
                'class' => AttributesBehavior::class,
                'attributes' => [
                    'size' => [
                        \Yii\db\ActiveRecord::EVENT_BEFORE_INSERT => function ($event) {
                            // 如果 size 已经被手动设置（如导入场景包时），跳过远程获取
                            return $this->size ?? $this->getFileSize();
                        },
                    ],
                    'type' => [
                        \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => function ($event) {
                            // 如果 type 已经被手动设置（如导入场景包时），跳过远程获取
                            return $this->type ?? $this->getFileType();
                        },
                    ],

                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'filename', 'key'], 'required'],
            [['user_id', 'size'], 'integer'],
            [['created_at'], 'safe'],
            [['md5', 'type', 'url', 'filename', 'key'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'md5' => Yii::t('app', 'Md5'),
            'type' => Yii::t('app', 'Type'),
            'url' => Yii::t('app', 'Url'),
            'user_id' => Yii::t('app', 'User ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'filename' => Yii::t('app', 'Filename'),
            'size' => Yii::t('app', 'Size'),
            'key' => Yii::t('app', 'Key'),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeValidate()
    {
        parent::beforeValidate();
        if (!isset($this->user_id)) {
            $this->user_id = Yii::$app->user->id;
        }
        // ...custom code here...
        return true;
    }
    /**
     * Gets query for [[Pictures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPictures()
    {
        return $this->hasMany(Picture::className(), ['file_id' => 'id']);
    }

    /**
     * Gets query for [[Polygens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPolygens()
    {
        return $this->hasMany(Polygen::className(), ['file_id' => 'id']);
    }

    /**
     * Gets query for [[Polygens0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPolygens0()
    {
        return $this->hasMany(Polygen::className(), ['image_id' => 'id']);
    }

    /**
     * Gets query for [[Videos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideos()
    {
        return $this->hasMany(Video::className(), ['file_id' => 'id']);
    }

}
