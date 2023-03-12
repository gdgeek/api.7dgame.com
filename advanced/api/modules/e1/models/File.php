<?php

namespace api\modules\e1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\AttributesBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "file".
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
class File extends \yii\db\ActiveRecord

{

    private $header = null;
    private function getFileHeader()
    {
        if (isset($this->url) && $this->header == null) {
            $this->header = get_headers($this->owner->url, true);
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

    public function fields()
    {
        $fields = parent::fields();

        unset($fields['updater_id']);
        unset($fields['id']);
        unset($fields['user_id']);
        unset($fields['created_at']);
        unset($fields['size']);
        unset($fields['filename']);

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
                        \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => [$this, 'getFileSize'],
                    ],
                    'type' => [
                        \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => [$this, 'getFileType'],
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
