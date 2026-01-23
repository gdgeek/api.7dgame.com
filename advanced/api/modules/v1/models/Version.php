<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "version".
 *
 * @property int $id
 * @property string $name
 * @property int $number
 * @property string $created_at
 * @property string|null $info
 */
class Version extends \yii\db\ActiveRecord
{

    public static function upgrade()
    {
        // 目前只有一个版本，后续版本升级逻辑在这里添加
        $version = self::findOne(['number' => self::getCurrentVersionNumber()]);
        if (!$version) {
            $version = new self();
            $version->name = 'Initial Version';
            $version->number = self::getCurrentVersionNumber();
            $version->created_at = date('Y-m-d H:i:s');
            $version->info = 'The first version of the system.';
            $version->save();
        }
        return $version;
    }
    public static function getCurrentVersionNumber()
    {
        return 1;
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'number', 'created_at'], 'required'],
            [['number'], 'integer'],
            [['created_at', 'info'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'number' => Yii::t('app', 'Number'),
            'created_at' => Yii::t('app', 'Created At'),
            'info' => Yii::t('app', 'Info'),
        ];
    }

}
