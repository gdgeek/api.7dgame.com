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
* @property string|null $query
* @property string $created_at
* @property int $user_id
* @property string|null $generation
* @property string|null $check
* @property string|null $download
* @property int|null $resource_id
* @property string|null $name
*
* @property Resource $resource
* @property User $user
*/

class AiRodin extends \yii\db\ActiveRecord
{
    
    public function fields()
    {
        return [
            'id', 
            'created_at', 
            // 'user_id', 
            'resource_id', 
            'generation', 
            'check', 
            'download', 
            'query', 
            'name'
        ];
    }
    public function extraFields()
    {
        return ['resource', 
        'step'=>function($model){
            
            $conditions = [  
                'resource_id',  
                'download',  
                'check',  
                'generation',  
                'query'  
            ];  
            
            $step = 0;  
            if($this->query){
                foreach ($conditions as $index => $condition) {  
                    if ($this->$condition) {  
                        $step = count($conditions) - $index; // 从后往前计算步骤  
                        break; // 找到第一个满足条件的后立即退出循环  
                    }  
                }  
            }
            
            return $step;  
        }
    ];
}
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
        [['created_at', 'generation', 'check', 'download','query'], 'safe'],
        // [['user_id'], 'required'],
        [['user_id', 'resource_id'], 'integer'],
        [['name'], 'string', 'max' => 255], 
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
        'created_at' => 'Created At',
        'user_id' => 'User ID',
        'generation' => 'Generation',
        'check' => 'Check',
        'download' => 'Download',
        'resource_id' => 'Resource ID',
        'query' => 'Query',
        'name' => 'Name', 
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
