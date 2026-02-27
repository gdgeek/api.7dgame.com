<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 邮箱解绑请求表单
 */
class UnbindEmailForm extends Model
{
    /**
     * @var string 旧邮箱验证后签发的令牌
     */
    public $change_token;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['change_token', 'required', 'message' => 'change_token 不能为空'],
            ['change_token', 'trim'],
            ['change_token', 'string', 'max' => 255],
        ];
    }
}
