<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "edu_student".
 *
 * @property int $id
 * @property int $user_id
 * @property int $class_id
 *
 * @property EduClass $class
 * @property User $user
 */
class EduStudent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_student';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'class_id'], 'required'],
            [['user_id', 'class_id'], 'integer'],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduClass::className(), 'targetAttribute' => ['class_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            // user_id is unique, one student can only be in one class
            [['user_id'], 'unique', 'message' => 'This student is already in a class'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'class_id' => Yii::t('app', 'Class ID'),
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClass()
    {
        return $this->hasOne(EduClass::className(), ['id' => 'class_id']);
    }

    public function fields()
    {
        return [
            'id',
            'user' => function () {
                return $this->user ? $this->user->toArray(['username', 'nickname']) : null;
            },
            'class' => function () {
                if (!$this->class) {
                    return null;
                }
                return $this->class->toArray(['id', 'name', 'info'], ['image', 'school']);
                
            },
        ];
    }

    public function extraFields()
    {
        return [];
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
}
