<?php

namespace api\modules\e1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "knight".
 *
 * @property int $id
 * @property string|null $title
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $create_at
 * @property string $updated_at
 * @property string|null $info
 * @property string|null $data
 * @property int|null $image_id
 * @property int|null $mesh_id
 * @property string|null $schema
 * @property string|null $events
 *
 * @property User $author
 * @property File $image
 * @property Resource $mesh
 * @property User $updater
 */
class Knight extends \yii\db\ActiveRecord

{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_at', 'updated_at'],
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
        return 'knight';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'updater_id', 'image_id', 'mesh_id'], 'integer'],
            [['create_at', 'updated_at'], 'safe'],
            [['info', 'data', 'schema', 'events'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['mesh_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['mesh_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    public function extraFields()
    {
        return [
            'image',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'create_at' => 'Create At',
            'updated_at' => 'Updated At',
            'info' => 'Info',
            'data' => 'Data',
            'image_id' => 'Image ID',
            'mesh_id' => 'Mesh ID',
            'schema' => 'Schema',
            'events' => 'Events',
        ];
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
    public function getResourceIds()
    {
        $resourceIds = \api\modules\v1\helper\Meta2Resources::Handle(json_decode($this->data));
        return $resourceIds;
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
     * Gets query for [[Mesh]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMesh()
    {

        return $this->hasOne(Resource::className(), ['id' => 'mesh_id']);
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
}
