<?php

namespace api\modules\a1\models;

use api\modules\a1\models\File;
use api\modules\a1\models\Meta;
use api\modules\v1\models\User;
use api\modules\v1\models\VerseQuery;
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
* @property int|null $version
*
* @property Meta[] $metas
* @property User $author
* @property File $image_id0
* @property User $updater

*/
class Game extends \yii\db\ActiveRecord

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
        return 'verse';
    }
    
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['author_id', 'updater_id', 'image_id', 'version'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info', 'data'], 'string'],
            [['name'], 'string', 'max' => 255],
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
        unset($fields['created_at']);
        unset($fields['author_id']);
        unset($fields['data']);
        unset($fields['version']);
        unset($fields['info']);
        $info = json_decode($this->info);
        $fields['name'] = function ($model) use ($info) {
            return $info->name;
        };
        $fields['description'] = function ($model) use ($info) {
            return $info->description;
        };
        
        return $fields;
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
            'name' => 'Name',
            'info' => 'Info',
            'image_id' => 'Image Id',
            'data' => 'Data',
            'version' => 'Version',
        ];
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
