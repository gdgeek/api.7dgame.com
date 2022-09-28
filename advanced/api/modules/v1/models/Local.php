<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "local".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class Local extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    /**
     * {@inheritdoc}
     * @return LocalQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LocalQuery(get_called_class());
    }
}
