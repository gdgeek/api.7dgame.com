<?php

namespace api\modules\p1\models;

use api\modules\v1\models\User;
use api\modules\v1\models\VerseQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "verse".
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property string|null $info
 * @property int|null $image_id
 * @property string|null $data
 * @property int|null $version
 *
 * @property Meta[] $metas
 * @property User $author
 * @property File $image_id0
 * @property User $updater

 */
class Verse extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['author_id', 'updater_id', 'image_id', 'version'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info', 'data'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['updater_id']);
        unset($fields['updated_at']);
        unset($fields['author_id']);
        unset($fields['version']);
        unset($fields['data']);
        unset($fields['image_id']);
        unset($fields['info']);
        $fields['image'] = function () {
            return $this->image->url;
        };
        $fields['description'] = function () {
            return json_decode($this->info)->description;
        };
        return $fields;
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
            'info' => 'Info',
            'image_id' => 'Image Id',
            'data' => 'Data',
            'version' => 'Version',
        ];
    }

    /**
     * Gets query for [[VerseEvents]].
     *
     * @return \yii\db\ActiveQuery|VerseEventQuery
     */
    public function getVerseEvent()
    {
        return $this->hasOne(VerseEvent::className(), ['verse_id' => 'id']);
    }
    /**
     * Gets query for [[MetaKnights]].
     *
     * @return \yii\db\ActiveQuery|MetaKnightQuery
     */
    public function getMetaKnights()
    {
        return $this->hasMany(MetaKnight::className(), ['verse_id' => 'id']);
    }

    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMetas()
    {
        return $this->hasMany(Meta::className(), ['verse_id' => 'id']);
    }
    /**
     * Gets query for [[VerseOpens]].
     *
     * @return \yii\db\ActiveQuery|VerseOpenQuery
     */
    public function getVerseOpen()
    {
        return $this->hasOne(VerseOpen::className(), ['verse_id' => 'id']);
    }

    public function getMessage()
    {
        return $this->hasOne(Message::class, ['id' => 'message_id'])
            ->viaTable('verse_open', ['verse_id' => 'id']);
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
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|FileQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
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
     * {@inheritdoc}
     * @return VerseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseQuery(get_called_class());
    }
    /**
     * Gets query for [[VerseRetes]].
     *
     * @return \yii\db\ActiveQuery|VerseReteQuery
     */
    public function getVerseRetes()
    {
        return $this->hasMany(VerseRete::className(), ['verse_id' => 'id']);
    }
    public function getShare()
    {

        $share = VerseShare::findOne(['verse_id' => $this->id, 'user_id' => Yii::$app->user->id]);

        return $share;
    }

}