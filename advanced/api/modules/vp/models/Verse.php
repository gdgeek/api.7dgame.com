<?php

namespace api\modules\vp\models;

//use api\modules\a1\models\EventLink;
use api\modules\a1\models\File;
use api\modules\a1\models\Meta;
use api\modules\a1\models\Resource;
use api\modules\a1\models\VerseScript;

use api\modules\vp\models\User;
use api\modules\v1\models\MultilanguageVerse;
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
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    
    public function fields()
    {
    
      $language = Yii::$app->request->get('language');
      if(!isset($language)){
          $language = 'en-us';
      }
      $context = MultilanguageVerse::find()->where(['verse_id' => $this->id, 'language' => $language])->one();
      return [
          'id',
          'metas',
          'name' => function() use($context){
              if(isset($context)){
                  return $context->name;
              }    
              return $this->name;
          },
          'description' => function() use($context){
              if(isset($context)){
                  return $context->description;
              }    
              $info = json_decode($this->info);
              return $info->description;
          },
          'uuid' => function () {
              if (empty($this->uuid)) {
                  $this->uuid = \Faker\Provider\Uuid::uuid();
                  $this->save();
              }
              return $this->uuid;
          },
          'data' => function () {
              if (!is_string($this->data)) {
                return json_encode($this->data);
              }
              return $this->data;
              
          },
          'code' => function () {
              $script = $this->script;
              if ($this->script) {
                  return $this->script->script;
              }
          },
          'resources',
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
            'info' => 'Info',
            'uuid' => 'Uuid',
            'image_id' => 'Image Id',
            'data' => 'Data',
            'version' => 'Version',
        ];
    }
    /**
     * Gets query for [[VerseCybers]].
     *
     * @return \yii\db\ActiveQuery|VerseCyberQuery
     */
    public function getVerseCybers()
    {
        return $this->hasMany(VerseCyber::className(), ['verse_id' => 'id']);
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


    public function getNodes($inputs, $quest)
    {
        $m = [];
        $UUID = [];
        foreach ($inputs as $child) {
            $id = $child->parameters->id;
            $UUID[$id] = $child->parameters->uuid;
            array_push($m, $id);
        }

        $datas = $quest->where(['id' => $m])->all();

        foreach ($datas as $i => $item) {
            if (!$item->uuid) {
                $item->uuid = $UUID[$item->id];
                $item->save();
            }
        }

        return $datas;
    }

    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMetas()
    {
        $ret = [];
        $data = $this->data;

        if (isset($data->children)) {
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

        return $share != null;
    }

    /**
     * Gets query for [[VerseScripts]].
     *
     * @return \yii\db\ActiveQuery|VerseScriptQuery
     */
    public function getVerseScripts()
    {
        return $this->hasMany(VerseScript::className(), ['verse_id' => 'id']);
    }
    public function getScript()
    {
        return $this->hasOne(VerseScript::className(), ['verse_id' => 'id']);
    }

}
