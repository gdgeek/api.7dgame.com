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
class Map extends \yii\db\ActiveRecord
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
     * @return MapQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MapQuery(get_called_class());
    }
}
