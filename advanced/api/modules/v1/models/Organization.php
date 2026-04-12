<?php

namespace api\modules\v1\models;

use yii\behaviors\TimestampBehavior;

class Organization extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%organization}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function rules()
    {
        return [
            [['title', 'name'], 'filter', 'filter' => 'trim'],
            [['name'], 'filter', 'filter' => 'strtolower'],
            [['title', 'name'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 64],
            [['name'], 'match', 'pattern' => '/^[a-z0-9][a-z0-9-]*$/'],
            [['name'], 'unique'],
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('{{%user_organization}}', ['organization_id' => 'id']);
    }

    public static function find()
    {
        return new OrganizationQuery(static::class);
    }
}
