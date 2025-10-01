<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
use api\modules\v1\models\User;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseTags;
use api\modules\v1\models\VerseCode;

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
* @property Manager[] $managers
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
     * Gets query for [[Managers]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */
    public function getManagers()
    {
        return $this->hasMany(Manager::className(), ['verse_id' => 'id']);
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
            [['created_at', 'updated_at', 'info', 'data'], 'safe'],
            [['name', 'uuid', 'description'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
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
        unset($fields['script']);
        $fields['description'] = function () {
            return $this->description;
        };
        $fields['editable'] = function () {
            return $this->editable();
        };
        $fields['viewable'] = function () {
            return $this->viewable();
        };

        $fields['info'] = function () {

            return $this->info;
        };
        $fields['data'] = function () {

            return $this->data;
        };

        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'author_id' => Yii::t('app', 'Author ID'),
            'updater_id' => Yii::t('app', 'Updater ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Name'),
            'info' => Yii::t('app', 'Info'),
            'data' => Yii::t('app', 'Data'),
            'image_id' => Yii::t('app', 'Image ID'),
            'version' => Yii::t('app', 'Version'),
            'uuid' => Yii::t('app', 'Uuid'),
            'description' => Yii::t('app', 'Description'),
        ];
    }


    /**
     * Gets query for [[VerseCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseCode()
    {

        $quest = $this->hasOne(VerseCode::className(), ['verse_id' => 'id']);
        $code = $quest->one();
        if ($code == null) {

            $code = new VerseCode();
            $code->verse_id = $this->id;
            $code->save();

        }

        return $quest;
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
    /*
        public function getSpace()
        {
            if (is_string($this->data)) {
                $data = json_decode($this->data);
            } else {
                $data = json_decode(json_encode($this->data));
            }
            if (isset($data->parameters) && isset($data->parameters->space)) {
                $space = $data->parameters->space;
                $model = Space::findOne($space->id);
                if ($model) {
                    return $model->model;
                }

            }
        }*/
    public function extraFields()
    {

        return [
            'metas',
            'image',
            'author',
            'public',
            'description',
            'resources',
            'verseCode',
            'verseTags',
            'tags',
        ];

    }



    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMetas()
    {
        $ret = [];
        if (is_string($this->data)) {
            $data = json_decode($this->data);
        } else {
            $data = json_decode(json_encode($this->data));
        }
        if (isset($data->children) && isset($data->children->modules)) {
            foreach ($data->children->modules as $item) {

                if (isset($item->parameters->meta_id)) {
                    $ret[] = $item->parameters->meta_id;
                }

            }
        }

        return Meta::find()->where(['id' => $ret])->all();

    }

    /**
     * Gets query for [[VerseTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseTags()
    {
        return $this->hasMany(VerseTags::className(), ['verse_id' => 'id']);
    }

    /**
     * Gets tag names associated with this verse
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        // 方法一：如果 VerseTags 有关联到 Tag 模型
        return $this->hasMany(Tags::className(), ['id' => 'tags_id'])
            ->viaTable('verse_tags', ['verse_id' => 'id']);
    }


    public function getPublic()
    {
        $tag = $this->getTags()->andWhere(['key' => 'public'])->one();
        if ($tag) {
            return true;
        }
        return false;
    }

    public function editable()
    {
        if (
            isset(Yii::$app->user->identity)
            && Yii::$app->user->identity->id == $this->author_id
        ) {
            return true;
        }

        return false;
    }
    public function viewable()
    {

        if ($this->getPublic() || $this->editable()) {
            return true;
        }

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
