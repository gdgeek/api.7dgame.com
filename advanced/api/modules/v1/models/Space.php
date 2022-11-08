<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "space".
 *
 * @property int $id
 * @property int $author_id
 * @property int $sample_id
 * @property int $mesh_id
 * @property int $dat_id
 * @property string $created_at
 * @property int|null $image_id
 * @property string|null $info
 * @property string|null $name
 *
 * @property User $author
 * @property File $dat
 * @property File $image
 * @property File $mesh
 * @property File $sample
 */
class Space extends \yii\db\ActiveRecord
{

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
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'space';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'sample_id', 'mesh_id', 'dat_id'], 'required'],
            [['author_id', 'sample_id', 'mesh_id', 'dat_id', 'image_id'], 'integer'],
            [['created_at'], 'safe'],
            [['info'], 'string'],
            [['title', 'name'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['dat_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['dat_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['mesh_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['mesh_id' => 'id']],
            [['sample_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['sample_id' => 'id']],
        ];
    }
    public function extraFields()
    {
        return [
            'image',
            'mesh',
            'sample',
            'dat',
            'author' => function () {
                return $this->author;
            },
        ];
    }
    public function afterDelete()
    {
        parent::afterDelete();
        $file = File::findOne($this->sample_id);
        if ($file) {
            $file->delete();
        }
        $file = File::findOne($this->mesh_id);
        if ($file) {
            $file->delete();
        }
        $file = File::findOne($this->dat_id);
        if ($file) {
            $file->delete();
        }
        $image = File::findOne($this->image_id);
        if ($image) {
            $image->delete();
        }
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
            'sample_id' => 'Sample ID',
            'mesh_id' => 'Mesh ID',
            'dat_id' => 'Dat ID',
            'created_at' => 'Created At',
            'image_id' => 'Image ID',
            'info' => 'Info',
            'name' => 'Name',
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
    public function getModel()
    {
        return ['title' => $this->title, 'name' => $this->name, 'image' => $this->image, 'mesh' => $this->mesh, 'sample' => $this->sample, 'dat' => $this->dat];
    }
    /**
     * Gets query for [[Dat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDat()
    {
        return $this->hasOne(File::className(), ['id' => 'dat_id']);
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
        return $this->hasOne(File::className(), ['id' => 'mesh_id']);
    }

    /**
     * Gets query for [[Sample]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSample()
    {
        return $this->hasOne(File::className(), ['id' => 'sample_id']);
    }

    /**
     * {@inheritdoc}
     * @return SpaceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SpaceQuery(get_called_class());
    }
}
