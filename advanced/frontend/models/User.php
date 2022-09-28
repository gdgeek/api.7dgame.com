<?php

namespace frontend\models;


use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class User extends \api\modules\v1\models\User
{



    /**
    * ����accessToken�ַ���
    * @return string
    * @throws \yii\base\Exception
    */
    public function generateAccessToken()
    {
        $this->access_token=Yii::$app->security->generateRandomString();
		
        return $this->access_token;
    }
    public static function findByAccessToken($accessToken){
        return User::findOne(["access_token" => $accessToken]);
    }
  

}
