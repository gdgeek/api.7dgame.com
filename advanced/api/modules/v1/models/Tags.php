<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $key
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
            [['name', 'key'], 'string', 'max' => 255],
            //name 和 key 必须提供
            [['name', 'key'], 'required'],
            ['key', 'validateClassificationKey'],
            [['name','key'], 'unique'],
        ];
    }

    public function validateClassificationKey($attribute): void
    {
        if (!$this->isNewRecord && !$this->isAttributeChanged($attribute)) {
            return;
        }

        if (strcasecmp(trim((string) $this->$attribute), 'public') === 0) {
            $this->addError($attribute, 'The public key is reserved for scene properties.');
        }
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
