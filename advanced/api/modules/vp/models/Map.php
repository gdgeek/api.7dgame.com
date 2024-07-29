<?php

namespace api\modules\vp\models;

use Yii;

/**
 * This is the model class for table "vp_map".
 *
 * @property int $id
 * @property int $page
 * @property string|null $info
 */
class VpMap extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vp_map';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page'], 'required'],
            [['page'], 'integer'],
            [['info'], 'string'],
            [['page'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'page' => 'Page',
            'info' => 'Info',
        ];
    }

    /**
     * {@inheritdoc}
     * @return VpMapQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VpMapQuery(get_called_class());
    }
}
