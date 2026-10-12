<?php

namespace backend\models;

class IdentityAdminUserView extends \yii\base\Model
{
    public $id;
    public $username;
    public $nickname;
    public $email;
    public $status;
    public $created_at;
    public $updated_at;
}
