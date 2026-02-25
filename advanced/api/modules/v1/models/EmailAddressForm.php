<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 邮箱地址表单
 */
class EmailAddressForm extends Model
{
    /**
     * @var string 邮箱地址
     */
    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'required', 'message' => '邮箱地址不能为空'],
            ['email', 'trim'],
            ['email', 'email', 'message' => '邮箱格式不正确'],
            ['email', 'string', 'max' => 255],
        ];
    }
}
