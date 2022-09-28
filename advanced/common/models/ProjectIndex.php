<?php

namespace common\models;
use api\modules\v1\models\User;

use Yii;

/**
 * This is the model class for table "project_index".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $author_id
 * @property int|null $image_id
 * @property int|null $data_id
 * @property string $created_at
 *
 * @property User $author
 * @property ProjectData $data
 * @property File $image
 */
class ProjectIndex extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_index';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'author_id'], 'required'],
            [['author_id', 'image_id', 'data_id'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'description'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['data_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectData::className(), 'targetAttribute' => ['data_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
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
            'description' => 'Description',
            'author_id' => 'Author ID',
            'image_id' => 'Image ID',
            'data_id' => 'Data ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Data]].
     *
     * @return \yii\db\ActiveQuery|ProjectDataQuery
     */
    public function getData()
    {
        return $this->hasOne(ProjectData::className(), ['id' => 'data_id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }

    /**
     * {@inheritdoc}
     * @return ProjectIndexQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProjectIndexQuery(get_called_class());
    }
}
