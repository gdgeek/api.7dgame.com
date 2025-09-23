<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "phototype".
 *
 * @property int $id
 * @property string|null $name
 * @property int $author_id
 * @property string|null $title
 * @property string|null $data
 * @property string|null $schema
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $uuid
 * @property int|null $image_id
 * @property int|null $updater_id
 * @property int|null $resource_id 
 *
 * @property User $author
 * @property File $image
 * @property Resource $resource
 * @property User $updater
 */
class Phototype extends \yii\db\ActiveRecord
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
        return 'phototype';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'image_id', 'updater_id', 'resource_id'], 'integer'],
            [['data', 'schema', 'created_at', 'updated_at'], 'safe'],
            [['title', 'uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['resource_id' => 'id']], 
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'author_id' => Yii::t('app', 'Author ID'),
            'data' => Yii::t('app', 'Data'),
            'schema' => Yii::t('app', 'Schema'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'uuid' => Yii::t('app', 'Uuid'),
            'image_id' => Yii::t('app', 'Image ID'),
            'updater_id' => Yii::t('app', 'Updater ID'),
            'resource_id' => Yii::t('app', 'Resource ID'), 
        ];
    }
	/**
    * Gets query for [[Resource]]. 
    * 
    * @return \yii\db\ActiveQuery 
    */ 
    public function getResource() 
    { 
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']); 
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
     * @return \yii\db\ActiveQuery
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

    public function extraFields()
    {
        return [
            'image',
            'author',
            'resource',
        ];
    }
}
