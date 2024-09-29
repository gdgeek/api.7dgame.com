<?php

namespace api\modules\v1\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use Yii;

/**
* This is the model class for table "ai_rodin".
*
* @property int $id
* @property string $token
* @property string|null $prompt
* @property int|null $image_id
* @property string|null $status
* @property string $created_at
* @property int $user_id
*
* @property File $image
* @property User $user
*/
class AiRodin extends \yii\db\ActiveRecord
{
    
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            
            if ($insert) {
                $this->token = Yii::$app->security->generateRandomString();
            }
            return true; // 返回 true 以继续保存
        }
        return false; // 返回 false 以阻止保存
    }
    
    /**
    * {@inheritdoc}
    */
    public static function tableName()
    {
        return 'ai_rodin';
    }
    
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['image_id', 'user_id'], 'integer'],
            [['status', 'created_at'], 'safe'],
            [['token', 'prompt'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
    
    /**
    * {@inheritdoc}
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'prompt' => 'Prompt',
            'image_id' => 'Image ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
        ];
    }
    
    /**
    * Gets query for [[Image]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }
    
    /**
    * Gets query for [[User]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
