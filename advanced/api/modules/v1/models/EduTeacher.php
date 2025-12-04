<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "edu_teacher".
 *
 * @property int $id
 * @property int $user_id
 * @property int $class_id
 *
 * @property EduClass $class
 * @property User $user
 */
class EduTeacher extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_teacher';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'class_id'], 'required'],
            [['user_id', 'class_id'], 'integer'],
            // user_id 和 class_id 联合唯一，同一教师不能重复添加到同一班级
            [['user_id'], 'unique', 'targetAttribute' => ['user_id', 'class_id'], 'message' => 'This teacher is already in this class'],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduClass::className(), 'targetAttribute' => ['class_id' => 'id']],
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
        ];
    }

    public function extraFields()
    {
        return ['class'];
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
