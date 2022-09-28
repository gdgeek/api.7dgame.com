<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "info".
 *
 * @property int $id
 * @property string $name
 * @property string $company
 * @property string $tel
 * @property string $reason
 */
class Info extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'company', 'tel', 'reason'], 'required'],
            [['name'], 'string', 'max' => 10],
            [['company'], 'string', 'max' => 50],
            [['tel'], 'string', 'max' => 11],
			[['reason', 'invitation'], 'string', 'max' => 255],
			[['tel'],'match','pattern'=>'/^1[34578]\d{9}$/','message'=>'需要正确的电话格式'], 
            [['tel'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '姓名',
            'company' => '您所在的企业',
            'tel' => '您的微信',
            'reason' => '申请测试 http://MrPP.com 的原因',
            'invitation' => '邀请码',
        ];
    }

    /**
     * {@inheritdoc}
     * @return InfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InfoQuery(get_called_class());
    }
}
