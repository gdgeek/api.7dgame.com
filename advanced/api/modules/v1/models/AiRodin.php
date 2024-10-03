<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
* This is the model class for table "ai_rodin".
*
* @property int $id
* @property string|null $prompt
* @property string $created_at
* @property int $user_id
* @property string|null $generation
* @property string|null $check
* @property string|null $download
* @property int|null $resource_id
*
* @property Resource $resource
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
            [['created_at', 'generation', 'check', 'download'], 'safe'],
            // [['user_id'], 'required'],
            [['user_id', 'resource_id'], 'integer'],
            [['prompt'], 'string', 'max' => 255],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['resource_id' => 'id']],
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
            'prompt' => 'Prompt',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
            'generation' => 'Generation',
            'check' => 'Check',
            'download' => 'Download',
            
            'resource_id' => 'Resource ID',
        ];
    }
    
    /**
    * Gets query for [[Resource]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getResource()
    {
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']);
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
