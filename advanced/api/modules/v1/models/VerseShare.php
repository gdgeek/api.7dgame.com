<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_share".
 *
 * @property int $id
 * @property int $verse_id
 * @property int|null $user_id
 * @property string|null $info
 *
 * @property User $user
 * @property Verse $verse
 */
class VerseShare extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_share';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'user_id'], 'required'],
            [['verse_id', 'user_id', 'editable'], 'integer'],
            [['info'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['verse_id', 'user_id'], 'unique', 'targetAttribute' => ['verse_id', 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verse_id' => 'Verse ID',
            'user_id' => 'User ID',
            'info' => 'Info',
            'editable' => 'Editable',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id']);
        $fields['user'] = function () {
            return $this->user;
        };
        $fields['info'] = function(){
            if(!is_string($this->info) && !is_null($this->info)){
                return json_encode($this->info);
            }
            return $this->info;
        };
        return $fields;
    }
    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery|VerseQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }

    /**
     * {@inheritdoc}
     * @return VerseShareQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseShareQuery(get_called_class());
    }
}
