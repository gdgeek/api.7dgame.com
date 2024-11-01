<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "code".
 *
 * @property int $id
 * @property string|null $lua
 * @property string|null $js
 *
 * @property MetaCode $metaCode
 * @property VerseCode $verseCode
 */
class Code extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lua', 'js'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lua' => 'Lua',
            'js' => 'Js',
        ];
    }

    /**
     * Gets query for [[MetaCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMetaCode()
    {
        return $this->hasOne(MetaCode::className(), ['code_id' => 'id']);
    }

    /**
     * Gets query for [[VerseCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseCode()
    {
        return $this->hasOne(VerseCode::className(), ['code_id' => 'id']);
    }
}
