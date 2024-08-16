<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;

use yii\helpers\Url;
use api\modules\v1\models\User;



/**
 * template module definition class
 */
class AppPost extends BaseObject
{
    
    private $username;
    private $password;
    private $data;
    public function __construct($config = [])
    {
        // ... 在应用配置之前初始化
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $post = Yii::$app->request->post();
        if(!isset($post['username'])){
    	    $post = Yii::$app->request->get();
        }

        if(isset($post['username'])){
            $this->username = $post['username']; 
        }
        
        if(isset($post['password'])){
            $this->password = $post['password']; 
        }
        
        if(isset($post['data'])){
            $this->data = $post['data']; 
        }



        // custom initialization code goes here
    }

    public function setup()
    {
      
       $ret = new \stdClass();
     
       if($this->login($this->username, $this->password)){
            $ret->succeed = true;
        }else{
            $ret->error = "user no!";
            $ret->succeed = false;
        }
        
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data = $this->data;
        }
        return [$ret,$data];
    }


     protected function login($username, $password){
    
        $user = User::findByUsername($username);
        if (!$user || !$user->validatePassword($password)) {
           return false;
        }
        return Yii::$app->user->login($user, 0);
    }

}
