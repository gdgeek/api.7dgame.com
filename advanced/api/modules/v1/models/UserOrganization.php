<?php

namespace api\modules\v1\models;

class UserOrganization extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_organization}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'organization_id'], 'required'],
            [['user_id', 'organization_id'], 'integer'],
            [['user_id', 'organization_id'], 'unique', 'targetAttribute' => ['user_id', 'organization_id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['organization_id'], 'exist', 'targetClass' => Organization::class, 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }
}
