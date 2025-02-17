<?php

namespace api\common\models;

use api\modules\v1\components\Validator\JsonValidator;
use api\modules\v1\models\File;
use api\modules\v1\models\UserInfo;
use yii\base\Model;

/**
* This is the model class for table "verse".
*
*/
class UserDataForm extends Model
{
    public $nickname;
    public $avatar_id;
    public $info;
    
    private $_user;
    
    /**
    * Creates a form model with given token.
    *
    * @param string $token
    * @param array $config name-value pairs that will be used to initialize the object properties
    * @throws InvalidArgumentException if token is empty or not valid
    */
    public function __construct($user, array $config = [])
    {
        $this->_user = $user;
        $this->nickname = $this->_user->nickname;

        if($this->_user->userInfo){
            $this->info = $this->_user->userInfo->info;
            $this->avatar_id = $this->_user->userInfo->avatar_id;
        }
       
        parent::__construct($config);
    }
    public function save()
    {
        if ($this->validate()) {
            $this->_user->nickname = $this->nickname;
            $info = null;
            if($this->_user->userInfo){
                $info = $this->_user->userInfo;
               
            }else{
                $info = new UserInfo();
                $info->user_id = $this->_user->id;
                
            }
            
            $info->info = $this->info;
            $info->avatar_id = $this->avatar_id;
            $info->save();
            
            if ($this->_user->validate()) {
                $this->_user->save();
                return true;
            }else{
                throw new \Exception($this->_user->getError());
            }
           
        } else {
            throw new \Exception($this->_user->getError());
        }
    }
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['nickname'], 'string'],
            [['info'], JsonValidator::class],
            [['avatar_id'], 'integer'],
            [['avatar_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['avatar_id' => 'id']],
        ];
    }
    
}
