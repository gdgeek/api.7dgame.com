<?php

namespace api\modules\a1\models;

use Yii;

/**
 * This is the model class for table "vp_key_value".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class VpKeyValue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vp_key_value';
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        $fields['value'] = function ($model) {
            if(!is_string($model->value)){
                return json_encode($model->value);
            }
            return $model->value;
        };
        return $fields;
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
     * @return VpKeyValueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VpKeyValueQuery(get_called_class());
    }
}
