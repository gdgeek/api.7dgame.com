<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "refresh_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string|null $created_at
 *
 * @property User $user
 */
class UserLinked extends \yii\db\ActiveRecord
{
    public const LOGIN_CODE_TTL_SECONDS = 60;

        public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['created_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_linked';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'key'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['key'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'key' => Yii::t('app', 'Key'),
            'created_at' => Yii::t('app', 'Created At'),
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

    public function loginCodeExpiresAt(): int
    {
        $createdAt = strtotime((string)$this->created_at);
        if ($createdAt === false) {
            return 0;
        }

        return $createdAt + self::LOGIN_CODE_TTL_SECONDS;
    }

    public function isLoginCodeExpired(): bool
    {
        $expiresAt = $this->loginCodeExpiresAt();
        return $expiresAt <= 0 || $expiresAt <= time();
    }
}
