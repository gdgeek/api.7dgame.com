<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
use api\modules\v1\models\Knight;
use api\modules\v1\models\Space;
use api\modules\v1\models\User;
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
     * Gets query for [[VerseCybers]].
     *
     * @return \yii\db\ActiveQuery|VerseCyberQuery
     */
    public function getVerseCybers()
    {
        return $this->hasMany(VerseCyber::className(), ['verse_id' => 'id']);
    }
    public function getResources()
    {
        $datas = $this->datas;

        $ids = [];

        $metas = $datas['metas'];
        foreach ($metas as $meta) {

            $ids = array_merge_recursive($ids, $meta->resourceIds);

        }
        $knights = $datas['knights'];
        foreach ($knights as $knight) {

            $ids = array_merge_recursive($ids, $knight->resourceIds);
            // $ids = $ids + $knight->resourceIds;
            // return $ids;
        }
        $items = Resource::find()->where(['id' => $ids])->all();
        return $items;
    }
    /*
    public function extraProgramme()
    {
    $programme = \api\modules\v1\helper\MetaVerse2Programme::Handle($this);
    return $programme;
    }
     */
    public function upgrade($data)
    {
        $ret = false;
        if (isset($data->parameters) && isset($data->parameters->transfrom)) {

            $ret = true;
            $data->parameters->transform = $data->parameters->transfrom;
            unset($data->parameters->transfrom);
        }

        if (isset($data->chieldren)) {

            $ret = true;
            $data->children = $data->chieldren;
            unset($data->chieldren);
        }
        if (isset($data->children->entities)) {
            foreach ($data->children->entities as $entity) {
                if ($this->upgrade($entity)) {
                    $ret = true;

                }
            }
        }
        if (isset($data->children->addons)) {
            foreach ($data->children->addons as $addon) {
                if ($this->upgrade($addon)) {
                    $ret = true;
                }
            }
        }
        if (isset($data->children->components)) {
            foreach ($data->children->components as $component) {
                if ($this->upgrade($component)) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    public function afterFind()
    {

        parent::afterFind();
        $data = json_decode($this->data);
        $change = $this->upgrade($data);
        if ($change) {
            $this->data = json_encode($data);
            $this->save();
        }

    }

    public function getDatas()
    {
        $data = json_decode($this->data);

        $m = [];
        $k = [];

        foreach ($data->children->metas as $child) {
            if ($child->type == 'Meta') {
                $id = $child->parameters->id;
                array_push($m, $id);

            } else {
                $id = $child->parameters->id;
                array_push($k, $id);
            }
        }
        $knightQuery = $this->getMetaKnights()->where(['id' => $k]);
        $metaQuery = $this->getMetas()->where(['id' => $m]);

        return ['metas' => $metaQuery->all(), 'knights' => $knightQuery->all()];
    }

    public function getScript()
    {

        $cybers = $this->verseCybers;
        if (count($cybers) >= 1) {
            $cyber = array_shift($cybers);
            return $cyber->script;
        }

        return null;

    }
    public function getSpace()
    {
        $data = json_decode($this->data);
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

        return ['metas', 'verseCybers', 'verseOpen', 'message', 'image', 'share',
            'author' => function () {
                return $this->author;
            },
            'space' => function () {
                return $this->space;
            },
            'datas' => function () {
                return $this->datas;
            },
            'script' => function () {
                return $this->script;
            },
            'resources' => function () {
                return $this->resources;
            },

        ];
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
