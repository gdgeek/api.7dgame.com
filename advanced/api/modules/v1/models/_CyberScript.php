<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "cyber_script".
 *
 * @property int $id
 * @property int $cyber_id
 * @property string $language
 * @property string|null $script
 *
 * @property Cyber $cyber
 */
class CyberScript extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cyber_script';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cyber_id', 'language'], 'required'],
            [['cyber_id'], 'integer'],
            [['script'], 'string'],
            [['language'], 'string', 'max' => 255],
            [['cyber_id', 'language'], 'unique', 'targetAttribute' => ['cyber_id', 'language']],
            [['cyber_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cyber::className(), 'targetAttribute' => ['cyber_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cyber_id' => 'Cyber ID',
            'language' => 'Language',
            'script' => 'Script',
        ];
    }

    /**
     * Gets query for [[Cyber]].
     *
     * @return \yii\db\ActiveQuery|CyberQuery
     */
    public function getCyber()
    {
        return $this->hasOne(Cyber::className(), ['id' => 'cyber_id']);
    }

    /**
     * {@inheritdoc}
     * @return CyberScriptQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CyberScriptQuery(get_called_class());
    }
}
