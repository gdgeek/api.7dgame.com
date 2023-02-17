<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meta_resource".
 *
 * @property int $id
 * @property int $meta_id
 * @property int $resource_id
 *
 * @property Meta $meta
 * @property Resource $resource
 */
class MetaResource extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta_resource';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta_id', 'resource_id'], 'required'],
            [['meta_id', 'resource_id'], 'integer'],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['resource_id' => 'id']],
            [['meta_id', 'resource_id'], 'unique', 'targetAttribute' => ['message_id', 'tag_id']],
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
            'resource_id' => 'Resource ID',
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
     * Gets query for [[Resource]].
     *
     * @return \yii\db\ActiveQuery|ResourceQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']);
    }

    /**
     * {@inheritdoc}
     * @return MetaResourceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetaResourceQuery(get_called_class());
    }
}
