<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 请求密码重置表单模型
 * 
 * 用于验证请求密码重置的请求参数
 * 
 * @author Kiro AI
 * @since 1.0
 */
class RequestPasswordResetForm extends Model
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
            [
                'email', 
                'exist', 
                'targetClass' => User::class, 
                'targetAttribute' => 'email',
                'message' => '该邮箱未注册'
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => '邮箱地址',
        ];
    }
}
