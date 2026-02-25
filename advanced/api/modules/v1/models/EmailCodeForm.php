<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 邮箱验证码表单
 */
class EmailCodeForm extends Model
{
    /**
     * @var string 验证码
     */
    public $code;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['code', 'required', 'message' => '验证码不能为空'],
            ['code', 'trim'],
            ['code', 'string', 'min' => 6, 'max' => 6, 'tooShort' => '验证码必须是 6 位', 'tooLong' => '验证码必须是 6 位'],
            ['code', 'match', 'pattern' => '/^\d{6}$/', 'message' => '验证码必须是 6 位数字'],
        ];
    }
}
