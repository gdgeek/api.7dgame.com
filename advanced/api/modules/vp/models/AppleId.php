<?php

namespace api\modules\vp\models;

use Yii;

/**
 * This is the model class for table "apple_id".
 *
 * @property int $id
 * @property string $apple_id
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property int|null $user_id
 * @property string $created_at
 * @property string|null $token
 * @property int|null $vp_token_id 
 *
 * @property User $user
 * @property Token $vpToken 
 */
class AppleId extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apple_id';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apple_id'], 'required'],
            [['user_id', 'vp_token_id'], 'integer'],
            [['created_at'], 'safe'],
            [['apple_id', 'email', 'first_name', 'last_name', 'token'], 'string', 'max' => 255],
            [['apple_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['vp_token_id'], 'exist', 'skipOnError' => true, 'targetClass' => Token::className(), 'targetAttribute' => ['vp_token_id' => 'id']], 
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apple_id' => 'Apple ID',
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'token' => 'Token',
            'vp_token_id' => 'Vp Token ID', 
        ];
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
    		 
   /** 
    * Gets query for [[Token]]. 
    * 
    * @return \yii\db\ActiveQuery 
    */ 
   public function getVpToken() 
   { 
       return $this->hasOne(Token::className(), ['id' => 'vp_token_id']); 
   } 
}
