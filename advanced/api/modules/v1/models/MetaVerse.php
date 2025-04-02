<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
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
 *
 * @property Meta[] $metas
 * @property User $author
 * @property File $image_id0
 * @property User $updater

 */
class MetaVerse extends \yii\db\ActiveRecord

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
            [['author_id', 'updater_id', 'image_id'], 'integer'],
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
        unset($fields['author_id']);
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
    public function extraResources()
    {
        $metas = $this->links;
        $out = [];
        foreach ($metas as $meta) {
            if ($meta->data) {
                $resources = $meta->extraResources();
                foreach ($resources as $resource) {
                    if (!isset($out[$resource['id']])) {
                        $out[$resource['id']] = $resource;
                    }
                }
            }
        }
        return array_values($out);
    }
    public function extraProgramme()
    {
        $programme = \api\modules\v1\helper\MetaVerse2Programme::Handle($this);
        return $programme;
    }

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
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        $change = $this->upgrade($data);
        if ($change) {
            $this->data = json_encode($data);
            $this->save();
        }
    }

    public function getLinks()
    {
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        $metas = $this->metas;
        $map = [];
        foreach ($metas as $meta) {
            $map[$meta->id] = $meta;
        }
        $ret = [];
        foreach ($data->children->metas as $child) {
            $id = $child->parameters->meta->id;
            if (isset($map[$meta->id])) {
                array_push($ret, $map[$id]);
            }
        }
        return $ret;
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
    public function extraFields()
    {
        return ['metas',
            'links' => function () {
                return $this->getLinks();
            },
            'programme' => function () {
                return $this->extraProgramme();
            },
            'script' => function () {
                return $this->script;
            },
            'resources' => function () {
                return $this->extraResources();
            },

        ];
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
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[ImageId0]].
     *
     * @return \yii\db\ActiveQuery|FileQuery
     */
    public function getImageId0()
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
