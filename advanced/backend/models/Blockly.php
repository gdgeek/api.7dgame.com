<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blockly".
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $title
 * @property string|null $block
 * @property string|null $lua
 * @property string|null $value
 */
class Blockly extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blockly';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['block', 'lua', 'value'], 'string'],
            [['type', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'title' => Yii::t('app', 'Title'),
            'block' => Yii::t('app', 'Block'),
            'lua' => Yii::t('app', 'Lua'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return BlocklyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BlocklyQuery(get_called_class());
    }
}
