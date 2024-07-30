<?php

namespace api\modules\v1\models;

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
    public function fields()
    {
        $fields = parent::fields();
        $fields['guides'] = function () {
            return $this->guides;
        };
        return $fields;
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
    public function getGuides()
    {
        return $this->hasMany(VpGuide::className(), ['map_id' => 'id'])->orderBy(['order' => SORT_ASC]);
    }
    /**
     * {@inheritdoc}
     * @return MapQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VpMapQuery(get_called_class());
    }
}
