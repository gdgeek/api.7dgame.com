<?php

namespace api\modules\v1\models;

use Yii;

use yii\helpers\Url;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "snapshot".
 *
 * @property int $id
 * @property int $verse_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $uuid
 * @property string|null $code
 * @property string|null $data
 * @property string|null $image
 * @property string|null $metas
 * @property string|null $resources
 * @property string|null $created_at
 * @property int|null $author_id
 * @property int|null $created_by
 * @property string|null $type
 *
 * @property User $author
 * @property User $createdBy
 * @property Verse $verse
 */
class Snapshot extends \yii\db\ActiveRecord
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
                // 'createdByAttribute' => 'author_id',
                'updatedByAttribute' => 'created_by',
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'snapshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id', 'author_id', 'created_by'], 'integer'],
            [['code'], 'string'],
            [['data', 'image', 'metas', 'resources', 'created_at'], 'safe'],
            [['name', 'description', 'uuid', 'type'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'verse_id' => Yii::t('app', 'Verse ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'uuid' => Yii::t('app', 'Uuid'),
            'code' => Yii::t('app', 'Code'),
            'data' => Yii::t('app', 'Data'),
            'image' => Yii::t('app', 'Image'),
            'metas' => Yii::t('app', 'Metas'),
            'resources' => Yii::t('app', 'Resources'),
            'created_at' => Yii::t('app', 'Created At'),
            'author_id' => Yii::t('app', 'Author ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }

    public function fields()
    {

        return [];
    }
    public function extraFields()
    {
        return ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image'];
    }

    static function CreateById($verse_id)
    {
        $verse = \api\modules\private\models\Verse::findOne($verse_id);
        if (!$verse) {
            throw new \yii\web\NotFoundHttpException('Verse not found');
        }
  
       
        $snapshot = Snapshot::find()->where(['verse_id' =>$verse_id])->one();
        if (!$snapshot) {
            $snapshot = new Snapshot();
        }
        $snapshot->verse_id = $verse->id;
        $snapshot->name = $verse->name;
        $snapshot->description = $verse->description;
        $snapshot->uuid = $verse->uuid;
        $snapshot->code = $verse->code;

        $snapshot->data = $verse->data;
        $snapshot->metas = $verse->getMetas()->all();
        $snapshot->resources = $verse->resources;
        $snapshot->image = $verse->image;
        $snapshot->author_id = $verse->author_id;
        return $snapshot;
    }
}
