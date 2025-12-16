<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "edu_class_group".
 *
 * @property int $id
 * @property int $class_id
 * @property int $group_id
 *
 * @property EduClass $class
 * @property Group $group
 */
class EduClassGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_class_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'group_id'], 'required'],
            [['class_id', 'group_id'], 'integer'],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduClass::className(), 'targetAttribute' => ['class_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'class_id' => Yii::t('app', 'Class ID'),
            'group_id' => Yii::t('app', 'Group ID'),
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

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }
}
