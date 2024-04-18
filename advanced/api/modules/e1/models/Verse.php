<?php

namespace api\modules\e1\models;

use api\modules\v1\models\User;
use api\modules\v1\models\VerseOpen;
use api\modules\v1\models\VerseQuery;
use api\modules\v1\models\VerseShare;
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
        unset($fields['created_at']);
        unset($fields['info']);
        unset($fields['version']);
        unset($fields['name']);
        // unset($fields['data']);
        // unset($fields['data']);

        $fields['space'] = function () {return $this->space;};
        $fields['modules'] = function () {return $this->modules;};
        $fields['resources'] = function () {return $this->resources;};
        $fields['editable'] = function () {return $this->editable;};
        $fields['viewable'] = function () {return $this->viewable;};
        //$fields['root'] = function () {return json_decode($this->data);};
        return $fields;
    }

    public function getEditable()
    {
        return $this->editable();
    }
    public function editable()
    {

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
    public function getViewable()
    {
        return $this->viewable();
    }
    public function viewable()
    {
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

    public function getModules()
    {
        $data = json_decode($this->data);
        $metas = $this->getNodes($data->children->metas, $this->getMetas());
        $metaKnights = $this->getNodes($data->children->metaKnights, $this->getMetaKnights());
        return array_merge($metas, $metaKnights);
    }
    public function getResources()
    {
        $modules = $this->modules;
        $ids = [];
        foreach ($modules as $module) {
            $ids = array_merge_recursive($ids, $module->resourceIds);
        }
        $items = Resource::find()->where(['id' => $ids])->all();
        return $items;
    }
/*
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
return json_encode($cyber->script);
}

return null;

}
 */
    public function getVerseShare()
    {
        $share = VerseShare::findOne(['verse_id' => $this->id, 'user_id' => Yii::$app->user->id]);
        return $share;
    }
    public function getSpace()
    {
        $data = json_decode($this->data);
        if (isset($data->parameters) && isset($data->parameters->space)) {
            $space = $data->parameters->space;
            $model = Space::findOne($space->id);
            return $model;

        }
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

}
