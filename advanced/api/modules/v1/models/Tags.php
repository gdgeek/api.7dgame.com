<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $info
 * @property int $managed
 *
 * @property MessageTags[] $messageTags
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
            [['info'], 'string'],
            [['managed'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'info' => 'Info',
            'managed' => 'Managed',
        ];
    }

    /**
     * Gets query for [[MessageTags]].
     *
     * @return \yii\db\ActiveQuery|MessageTagsQuery
     */
    public function getMessageTags()
    {
        return $this->hasMany(MessageTags::className(), ['tag_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TagsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TagsQuery(get_called_class());
    }
}
