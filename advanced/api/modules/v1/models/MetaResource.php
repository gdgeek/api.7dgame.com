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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'meta_id' => Yii::t('app', 'Meta ID'),
            'resource_id' => Yii::t('app', 'Resource ID'),
        ];
    }

    /**
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
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
}
