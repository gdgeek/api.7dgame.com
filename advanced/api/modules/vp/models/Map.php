<?php

namespace api\modules\vp\models;

use api\modules\v1\components\Validator\JsonValidator;
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
            [['info'], JsonValidator::class],
            [['page'], 'unique'],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
       
        unset($fields['id']);
        $fields['info'] = function () {
            return JsonValidator::to_string($this->info);
        };
       // unset($fields['info']);
        $fields['count'] = function () {
            $count = Map::find()->count('*');
            return $count;
        };
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
        return $this->hasMany(Guide::className(), ['map_id' => 'id'])->orderBy(['order' => SORT_ASC]);
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
