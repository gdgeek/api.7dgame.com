<?php

namespace api\modules\v1\models;

use api\modules\v1\components\GroupOwnerRule;
use Yii;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $image_id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $info
 * @property string|null $description
 *
 * @property EduClassGroup[] $eduClassGroups
 * @property File $image
 * @property User $user
 * @property GroupUser[] $groupUsers
 * @property GroupVerse[] $groupVerses
 */
class Group extends \yii\db\ActiveRecord
{


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * 保存后清除创建者缓存
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // 清除缓存
        GroupOwnerRule::clearCache($this->user_id, $this->id);
        
        // 如果是更新且 user_id 发生变化，也清除旧创建者的缓存
        if (!$insert && isset($changedAttributes['user_id'])) {
            GroupOwnerRule::clearCache($changedAttributes['user_id'], $this->id);
        }
    }

    /**
     * 删除后清除创建者缓存
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // 清除缓存
        GroupOwnerRule::clearCache($this->user_id, $this->id);
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
         //   [['created_at', 'updated_at', 'user_id'], 'required'],
            [['created_at', 'updated_at', 'info'], 'safe'],
            [['description', 'name'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['image_id', 'user_id'], 'integer'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
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
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'image_id' => Yii::t('app', 'Image ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'info' => Yii::t('app', 'Info'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * Gets query for [[EduClassGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduClassGroups()
    {
        return $this->hasMany(EduClassGroup::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
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
     * Gets query for [[GroupUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[GroupVerses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupVerses()
    {
        return $this->hasMany(GroupVerse::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[Verses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerses()
    {
        return $this->hasMany(Verse::className(), ['id' => 'verse_id'])
            ->via('groupVerses');
    }

    /**
     * 检查当前用户是否已加入本组
     * 创建者也算已加入
     * @return bool
     */
    public function getJoined()
    {
        $userId = Yii::$app->user->id;
        if (!$userId) {
            return false;
        }
        
        // 创建者算已加入
        if ($this->user_id == $userId) {
            return true;
        }
        
        return GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $this->id])
            ->exists();
    }

    public function extraFields()
    {
        return ['image', 'user', 'groupUsers', 'groupVerses', 'verses', 'joined'];
    }
}
