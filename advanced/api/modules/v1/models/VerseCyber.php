<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "verse_cyber".
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property int|null $verse_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $data
 * @property string|null $code
 *
 * @property User $author
 * @property User $updater
 * @property Verse $verse
 */
class VerseCyber extends \yii\db\ActiveRecord
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
                'createdByAttribute' => 'author_id',
                'updatedByAttribute' => 'updater_id',
            ],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
       // unset($fields['id']);

        unset($fields['author_id']);
        unset($fields['updater_id']);
        unset($fields['verse_id']);
        // unset($fields['data']);
        unset($fields['created_at']);
        unset($fields['updated_at']);
        //unset($fields['data']);

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_cyber';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'updater_id', 'verse_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['data', 'script'], 'string'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'verse_id' => 'Verse ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'data' => 'Data',
            'script' => 'Script',
        ];
    }
    public function extraAuthor()
    {
        $author = $this->author;
        $result = new \stdClass();
        $result->id = $author->id;
        $result->username = $author->username;
        $result->nickname = $author->nickname;
        $result->email = $author->email;

        return $result;
    }
    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Updater]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
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
     * @return VerseCyberQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseCyberQuery(get_called_class());
    }
}
