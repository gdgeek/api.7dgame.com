<?php

namespace api\modules\v1\models;

use api\modules\v1\components\GroupMemberRule;
use Yii;

/**
 * This is the model class for table "group_user".
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 *
 * @property Group $group
 * @property User $user
 */
class GroupUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id'], 'required'],
            [['user_id', 'group_id'], 'integer'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            // user_id 和 group_id 联合唯一，同一用户不能重复加入同一小组
            [['user_id', 'group_id'], 'unique', 'targetAttribute' => ['user_id', 'group_id'], 'message' => 'This user is already in this group'],
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
            'group_id' => Yii::t('app', 'Group ID'),
        ];
    }

    /**
     * 保存后清除成员缓存
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // 清除缓存
        GroupMemberRule::clearCache($this->user_id, $this->group_id);

        // group 成员变更会影响 verse 的 editable 计算
        Verse::bumpUserGroupRevision((int)$this->user_id);
        
        // 如果是更新且 group_id 或 user_id 发生变化，也清除旧的缓存
        if (!$insert) {
            if (isset($changedAttributes['user_id'])) {
                GroupMemberRule::clearCache($changedAttributes['user_id'], $this->group_id);
                Verse::bumpUserGroupRevision((int)$changedAttributes['user_id']);
            }
            if (isset($changedAttributes['group_id'])) {
                GroupMemberRule::clearCache($this->user_id, $changedAttributes['group_id']);
            }
        }
    }

    /**
     * 删除后清除成员缓存
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // 清除缓存
        GroupMemberRule::clearCache($this->user_id, $this->group_id);

        // group 成员变更会影响 verse 的 editable 计算
        Verse::bumpUserGroupRevision((int)$this->user_id);
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
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
}
