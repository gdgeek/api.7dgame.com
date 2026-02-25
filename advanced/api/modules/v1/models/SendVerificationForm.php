<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\Model;

/**
 * 发送验证码表单模型
 * 
 * 用于验证发送邮箱验证码的请求参数
 * 
 * @author Kiro AI
 * @since 1.0
 */
class SendVerificationForm extends Model
{
    /**
     * @var string 邮箱地址
     */
    public $email;

    /**
     * @var string 完整 locale，例如 en-US
     */
    public $locale = 'en-US';

    /**
     * @var array 多语言文案，key 为 locale
     */
    public $i18n = [];
    
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
            // 检查邮箱是否已被其他用户使用
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'message' => '该邮箱已被其他用户使用',
                'filter' => function ($query) {
                    // 排除当前登录用户（如果存在且不是控制台应用）
                    if (!Yii::$app instanceof \yii\console\Application && 
                        isset(Yii::$app->user) && 
                        !Yii::$app->user->isGuest) {
                        $query->andWhere(['!=', 'id', Yii::$app->user->id]);
                    }
                }
            ],
            ['locale', 'default', 'value' => 'en-US'],
            ['locale', 'trim'],
            ['locale', 'match', 'pattern' => '/^[a-z]{2}-[A-Z]{2}$/', 'message' => 'locale 必须为完整格式，如 en-US'],
            ['i18n', 'default', 'value' => []],
            ['i18n', 'validateI18nPayload'],
        ];
    }

    public function validateI18nPayload($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        if ($this->$attribute === null) {
            $this->$attribute = [];
            return;
        }

        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'i18n 必须是对象，key 为 locale。');
            return;
        }

        foreach ($this->$attribute as $locale => $texts) {
            if (!is_string($locale) || !preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale)) {
                $this->addError($attribute, 'i18n 的 key 必须是完整 locale（如 en-US）。');
                return;
            }
            if (!is_array($texts)) {
                $this->addError($attribute, 'i18n 的每个 locale 文案必须是对象。');
                return;
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => '邮箱地址',
            'locale' => '语言地区',
            'i18n' => '多语言文案',
        ];
    }
}
