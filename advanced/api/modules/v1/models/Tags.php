<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $key
 * @property string $type
 *
 * @property VerseTags[] $verseTags
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
           
            [['type'], 'string'],
            [['name', 'key'], 'string', 'max' => 255],
            //name 和 key 必须提供
            [['name', 'key'], 'required'],
            [['name','key'], 'unique'],
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
            'key' => 'Key',
            'type' => 'Type',
        ];
    }

  

    /**
     * Gets query for [[VerseTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseTags()
    {
        return $this->hasMany(VerseTags::className(), ['tags_id' => 'id']);
    }
}
