<?php

namespace api\modules\v1\models;

use api\modules\v1\models\Cyber;
use api\modules\v1\models\File;
use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "meta".
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property string|null $info
 * @property int|null $image_id
 * @property string|null $data
 * @property string|null $uuid
 *
 * @property Cyber[] $cybers
 * @property User $author
 * @property File $image
 * @property User $updater
 * @property MetaRete[] $metaRetes
 * @property string|null $events
 * @property string|null $title
 *
 * @property MetaResource[] $metaResources
 */
class Meta extends \yii\db\ActiveRecord

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
        return 'meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'updater_id', 'image_id', 'custom'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info', 'data', 'events'], 'string'],
            [['uuid', 'title'], 'string', 'max' => 255],
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
        unset($fields['updated_at']);
        unset($fields['created_at']);
        $fields['image'] = function () {
            return $this->image;
        };
        $fields['custom'] = function () {
            return $this->custom;
        };
        $fields['resources'] = function () {
            return $this->resources;
        };

        $fields['editable'] = function () {return $this->editable();};
        $fields['viewable'] = function () {return $this->viewable();};
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
            'info' => 'Info',
            'image_id' => 'Image ID',
            'custom' => 'Custom',
            'data' => 'Data',
            'uuid' => 'Uuid',
        ];
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
        
        return true;
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
        return false;
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
    public function getResourceIds()
    {
        $resourceIds = \api\modules\v1\helper\Meta2Resources::Handle(json_decode($this->data));
        return $resourceIds;
    }
    public function getResources()
    {
        $resourceIds = $this->resourceIds;
        $items = Resource::find()->where(['id' => $resourceIds])->all();
        return $items;
    }

    /**
     * Gets query for [[Cybers]].
     *
     * @return \yii\db\ActiveQuery|CyberQuery
     */
    public function getCyber()
    {
        return $this->hasOne(Cyber::className(), ['meta_id' => 'id']);
    }

    /**
     * Gets query for [[MetaRetes]].
     *
     * @return \yii\db\ActiveQuery|MetaReteQuery
     */
    public function getMetaRetes()
    {
        return $this->hasMany(MetaRete::className(), ['meta_id' => 'id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }

    public function extraFields()
    {
        return ['image',
            'verseMetas',
            'author' => function () {
                return $this->author;
            },
            'script' => function () {
                if ($this->cyber) {
                    return $this->cyber->script;
                }
                return null;
            },
            'cyber',
        ];
    }


    /**
     * Gets query for [[VerseMeta]].
     *
     * @return \yii\db\ActiveQuery|VerseMetaQuery
     */
    public function getVerseMetas()
    {
        return $this->hasMany(VerseMeta::className(), ['meta_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetaQuery(get_called_class());
    }

}
