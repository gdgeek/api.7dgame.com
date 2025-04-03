<?php

namespace api\modules\private\models;




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
            [['created_at', 'updated_at', 'data'], 'safe'],
            [['info'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->uuid)) {
                $this->uuid = \Faker\Provider\Uuid::uuid();
            }

            return true;
        }
        return false;
    }
    public function extraFields()
    {


        return [
            'id',
            'name',
            'uuid',
            'metas' => function (): array {
                return $this->getMetas()->all();
            },
            'data',
            'image',
            'resources',
            'description',
            'code',
        ];
    }


    public function getCode()
    {
        $code = $this->verseCode;
        $cl = Yii::$app->request->get('cl');

        $substring = "";
        if (!$cl || $cl != 'js') {
            $cl = 'lua';
            $substring = "local verse = {}\n local is_playing = false\n";
        }
        if ($code && $code->code) {
            $script = $code->code->$cl;
        }

        if (isset($script)) {
            if (strpos($script, $substring) !== false) {
                return $script;
            } else {
                return $substring . $script;
            }
        } else {
            return $substring;
        }
    }
    public function getVerseCode()
    {
        return $this->hasOne(VerseCode::className(), ['verse_id' => 'id']);
    }
    public function fields()
    {
        return [];
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



    public function getResources()
    {
        $metas = $this->getMetas()->all();

        $ids = [];

        foreach ($metas as $meta) {
            $ids = array_merge_recursive($ids, $meta->getResourceIds());
        }

        return Resource::find()->where(['id' => $ids])->all();
      
    }


    public function getMetaIds()
    {
        $data = $this->data;
        if (!isset($data['children']) || !isset($data['children']['modules'])) {
            return [];
        }
        return array_map(function ($item) {
            return $item['parameters']['meta_id'] ?? null;
        }, $data['children']['modules']);
    }


    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */


    public function getMetas()
    {
        return Meta::find()->where(['id' => $this->getMetaIds()]);
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

