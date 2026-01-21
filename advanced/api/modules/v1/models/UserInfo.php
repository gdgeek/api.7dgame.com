<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;

use yii\caching\TagDependency;
use api\modules\v1\components\Validator\JsonValidator;
use Yii;
use OpenApi\Annotations as OA;

/**
 * This is the model class for table "user_info".
 *
 * @OA\Schema(
 *     schema="UserInfo",
 *     title="用户信息",
 *     description="用户详细信息模型",
 *     @OA\Property(property="id", type="integer", description="用户信息ID", example=1),
 *     @OA\Property(property="user_id", type="integer", description="用户ID", example=1),
 *     @OA\Property(property="info", type="string", description="用户信息JSON", example="{}"),
 *     @OA\Property(property="avatar_id", type="integer", description="头像文件ID", example=100),
 *     @OA\Property(property="gold", type="integer", description="金币数量", example=1000),
 *     @OA\Property(property="points", type="integer", description="积分数量", example=500)
 * )
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $info
 * @property int|null $avatar_id
 * @property int $gold
 * @property int $points
 *
 * @property File $avatar
 * @property User $user
 */
class UserInfo extends \yii\db\ActiveRecord
{

    public function  afterSave($insert, $changedAttributes)
    {
      
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(Yii::$app->cache, 'userinfo_cache');
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'avatar_id', 'gold', 'points'], 'integer'],
            [['info'], JsonValidator::class],
            [['avatar_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['avatar_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

 
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'info' => 'Info',
            'avatar_id' => 'Avatar ID',
            'gold' => 'Gold',
            'points' => 'Points',
        ];
    }

    public function extraFields()
    {
        return ['avatar'];
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['user_id']);
        unset($fields['avatar_id']);
        $fields['avatar'] = function ($model) {
            return $model->avatar;
        };
        
        return $fields;
    }

    /**
     * Gets query for [[Avatar]].
     *
     * @return \yii\db\ActiveQuery|FileQuery
     */
    public function getAvatar()
    {
        return $this->hasOne(File::class, ['id' => 'avatar_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return UserInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserInfoQuery(get_called_class());
    }
}
