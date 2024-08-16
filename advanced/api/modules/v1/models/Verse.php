<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
//use api\modules\v1\models\Knight;
use api\modules\v1\models\Space;
use api\modules\v1\models\User;
use api\modules\v1\models\VerseShare;
use api\modules\v1\models\MultilanguageVerse;

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
        unset($fields['image_id']);
        unset($fields['updated_at']);

        $fields['editable'] = function () {return $this->editable();};
        $fields['viewable'] = function () {return $this->viewable();};
      //  $fields['links'] = function () {return $this->eventLinks;};

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

    public function getResources()
    {
        $metas = $this->metas;

        $ids = [];

        foreach ($metas as $meta) {
            $ids = array_merge_recursive($ids, $meta->resourceIds);
        }

        $items = Resource::find()->where(['id' => $ids])->all();
        return $items;
    }

    public function afterFind()
    {

        parent::afterFind();
        if (empty($this->uuid)) {
            $this->uuid = \Faker\Provider\Uuid::uuid();
            $this->save();
        }
    }

    public function getSpace()
    {
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        if (isset($data->parameters) && isset($data->parameters->space)) {
            $space = $data->parameters->space;
            $model = Space::findOne($space->id);
            if ($model) {
                return $model->model;
            }

        }
    }
    public function extraFields()
    {

        return [
            'metas',
            'verseOpen',
            'message',
            'image',
            'author',
            'script',
            'resources',
            'verseShare',
            'languages',
        ];

    }

    /**
     * Gets query for [[EventLinks]].
     *
     * @return \yii\db\ActiveQuery|EventLinkQuery
     */
    /*
    public function getEventLinks()
    {
        return $this->hasMany(EventLink::className(), ['verse_id' => 'id']);
    }
    */


    /**
     * Gets query for [[Languages]].
     *
     * @return \yii\db\ActiveQuery|LanguageQuery
     */
    public function getLanguages(){
        return $this->hasMany(MultilanguageVerse::className(), ['verse_id' => 'id']);
    }
    /**
     * Gets query for [[MetaKnights]].
     *
     * @return \yii\db\ActiveQuery|MetaKnightQuery
    */
    /*
    public function getMetaKnights()
    {
    return $this->hasMany(MetaKnight::className(), ['verse_id' => 'id']);
    }*/
    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMetas()
    {
        $ret = [];
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        if (isset($data->children) && isset($data->children->modules)) {
            foreach ($data->children->modules as $item) {
                $ret[] = $item->parameters->meta_id;
            }
        }
        return Meta::find()->where(['id' => $ret])->all();

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
    public function editable()
    {
        if (!isset(Yii::$app->user->identity)) {
            return false;
        }
        $userid = Yii::$app->user->identity->id;
        if ($userid == $this->author_id) {
            return true;
        }
        $share = $this->verseShare;
        if ($share && $share->editable) {
            return true;
        }
        return false;
    }

    public function viewable()
    {
        if (!isset(Yii::$app->user->identity)) {
            return false;
        }
        $userid = Yii::$app->user->identity->id;
        if ($userid == $this->author_id) {
            return true;
        }
        $share = $this->verseShare;
        if ($share) {
            return true;
        }

        $open = $this->verseOpen;
        if ($open) {
            return true;
        }
        return false;
    }
    public function getScript()
    {
        return $this->hasOne(VerseScript::className(), ['verse_id' => 'id']);

    }
    public function getVerseShare()
    {

        $share = VerseShare::findOne(['verse_id' => $this->id, 'user_id' => Yii::$app->user->id]);
        return $share;
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

}
