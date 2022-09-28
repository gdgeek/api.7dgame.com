<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "scripts".
 *
 * @property int $id
 * @property string $key
 * @property string $script
 */
class Scripts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'scripts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['script'], 'string'],
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
            'script' => 'Script',
        ];
    }

    /**
     * {@inheritdoc}
     * @return ScriptsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ScriptsQuery(get_called_class());
    }
}
