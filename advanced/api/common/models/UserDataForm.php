<?php

namespace api\common\models;

use api\modules\v1\models\File;
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
        $info = $this->_user->userInfo;

        $this->info = $info->info;
        $this->avatar_id = $info->avatar_id;

       
        $this->nickname = $this->_user->nickname;
        parent::__construct($config);
    }
    public function save()
    {
        if ($this->validate()) {
            $this->_user->nickname = $this->nickname;

            $info = $this->_user->userInfo;

            $info->info = $this->info;
            $info->avatar_id = $this->avatar_id;
           
           
            if ($this->_user->validate()) {
                $this->_user->save();
                return true;
            }
            return false;
        } else {
            return false;
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
