<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "feedback".
 *
 * @property int $id
 * @property int $reporter
 * @property int $repairer
 * @property int $state_id
 * @property int $describe_id
 * @property string $bug
 * @property string $debug
 * @property string $infomation
 * @property string $create_at
 * @property string $fixed_at
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reporter', 'describe_id', 'bug'], 'required'],
            [['reporter', 'repairer', 'state_id', 'describe_id'], 'integer'],
            [['bug', 'debug', 'infomation'], 'string'],
            [['create_at', 'fixed_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reporter' => '提交者',
            'repairer' => '解决者',
            'state_id' => '处理状态',
            'describe_id' => '问题类型',
            'bug' => '问题描述',
            'debug' => '解决描述',
            'infomation' => '相关信息',
            'create_at' => '创建时间',
            'fixed_at' => '处理时间',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FeedbackQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedbackQuery(get_called_class());
    }
}
