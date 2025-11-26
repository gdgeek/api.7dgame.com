<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "edu_school".
 *
 * @property int $id
 * @property string|null $name
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $image_id
 * @property string|null $info
 * @property int|null $principal
 *
 * @property EduClass[] $eduClasses
 * @property File $image
 * @property User $principal0
 */
class EduSchool extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at', 'info'], 'safe'],
            [['image_id', 'principal'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['principal'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['principal' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'image_id' => Yii::t('app', 'Image ID'),
            'info' => Yii::t('app', 'Info'),
            'principal' => Yii::t('app', 'Principal'),
        ];
    }

    /**
     * Gets query for [[EduClasses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduClasses()
    {
        return $this->hasMany(EduClass::className(), ['school_id' => 'id']);
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
     * Gets query for [[Principal0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrincipal0()
    {
        return $this->hasOne(User::className(), ['id' => 'principal']);
    }
}
