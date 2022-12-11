<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meta_event".
 *
 * @property int $id
 * @property int $meta_id
 * @property string|null $slots
 * @property string|null $links
 *
 * @property Meta $meta
 */
class MetaEvent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta_event';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta_id'], 'required'],
            [['meta_id'], 'integer'],
            [['slots', 'links'], 'string'],
            [['meta_id'], 'unique'],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meta_id' => 'Meta ID',
            'slots' => 'Slots',
            'links' => 'Links',
        ];
    }

    /**
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
    }

    /**
     * {@inheritdoc}
     * @return MetaEventQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetaEventQuery(get_called_class());
    }
}
