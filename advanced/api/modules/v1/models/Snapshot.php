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
 * @property string|null $uuid
 * @property string|null $code
 * @property string|null $data
 * @property string|null $metas
 * @property string|null $resources
 * @property string|null $space
 * @property string|null $created_at
 * @property string|null $description
 * @property int|null $created_by
 * @property string|null $managers 
 *
 * @property User $author
 * @property User $createdBy
 * @property Verse $verse
 */
class Snapshot extends \yii\db\ActiveRecord
{
    public const TAKE_PHOTO_EXTRA_FIELDS = [
        'code',
        'id',
        'name',
        'data',
        'description',
        'metas',
        'resources',
        'uuid',
        'image',
        'space',
    ];

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
            [['verse_id', 'created_by'], 'integer'],
            [['code'], 'string'],
            [['data', 'metas', 'resources', 'space', 'created_at', 'managers'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            //    [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }

    public function beforeValidate()
    {
        foreach (['metas', 'resources', 'space', 'managers'] as $attribute) {
            if (is_array($this->$attribute) || is_object($this->$attribute)) {
                $value = self::jsonReadyValue($this->$attribute);
                $this->$attribute = self::isNativeJsonColumn($attribute)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'verse_id' => Yii::t('app', 'Verse ID'),

            'uuid' => Yii::t('app', 'Uuid'),
            'code' => Yii::t('app', 'Code'),
            'data' => Yii::t('app', 'Data'),

            'metas' => Yii::t('app', 'Metas'),
            'resources' => Yii::t('app', 'Resources'),
            'space' => Yii::t('app', 'Space'),
            'created_at' => Yii::t('app', 'Created At'),

            'created_by' => Yii::t('app', 'Created By'),

            'managers' => Yii::t('app', 'Managers'),
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
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

        return [
            'id',
            'name' => function (): string {
                return $this->verse->name ?? "";
            },
            'description' => function (): string {

                return $this->verse->description ?? "";
            },
            'image' => function () {
                return $this->verse->getImage()->one();
            },
            'author_id' => function (): int {
                return $this->verse->author_id;
            },
            'uuid',
            'verse_id',
            'code',
            'data',
            'metas' => function () {
                return self::normalizeJsonSnapshot($this->metas);
            },
            'resources' => function () {
                return self::normalizeJsonSnapshot($this->resources);
            },
            'space' => function () {
                return self::normalizeJsonSnapshot($this->space);
            },
            'managers' => function () {
                return self::normalizeJsonSnapshot($this->managers);
            },
        ];
    }


    static function CreateById($verse_id)
    {
        $verse = \api\modules\v1\models\VerseSnapshot::find()
            ->with(['space.mesh', 'space.file', 'space.image'])
            ->where(['id' => $verse_id])
            ->one();
        if (!$verse) {
            throw new \yii\web\NotFoundHttpException('Verse not found');
        }


        $snapshot = Snapshot::find()->where(['verse_id' => $verse_id])->one();
        if (!$snapshot) {
            $snapshot = new Snapshot();
            $snapshot->verse_id = $verse->id;
        }

        $snapshot->uuid = $verse->uuid;
        $snapshot->code = $verse->code;
        $snapshot->data = json_encode($verse->data);
        $snapshot->managers = $verse->managers;



        $snapshot->metas = array_map(function ($meta) {
            return [
                'id' => $meta->id,
                'prefab' => $meta->prefab,
                'title' => $meta->title,
                'data' => json_encode($meta->data),
                'code' => $meta->code,
                'uuid' => $meta->uuid,
                'events' => json_encode(value: $meta->events),
                'type' => $meta->prefab == 0 ? 'entity' : 'prefab',
            ];
        }, $verse->getMetas()->all());


        $snapshot->resources = $verse->getResources();
        $snapshot->space = self::buildSpaceSnapshot($verse->space);
        return $snapshot;
    }

    private static function buildSpaceSnapshot(?Space $space): ?array
    {
        if ($space === null) {
            return null;
        }

        return [
            'type' => self::spaceType($space),
            'image' => self::buildFileSnapshot($space->image),
            'mesh' => self::buildFileSnapshot($space->mesh),
            'file' => self::buildFileSnapshot($space->file),
        ];
    }

    private static function spaceType(Space $space): ?string
    {
        $data = Space::compactData($space->data, $space->image_id ? (int)$space->image_id : null);
        if (!is_array($data)) {
            return null;
        }

        $provider = $data['provider'] ?? null;
        return is_string($provider) && $provider !== '' ? $provider : null;
    }

    private static function buildFileSnapshot(?File $file): ?array
    {
        if ($file === null) {
            return null;
        }

        return $file->toArray(['id', 'md5', 'type', 'url', 'filename', 'size', 'key']);
    }

    private static function normalizeJsonSnapshot($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    private static function jsonReadyValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'jsonReadyValue'], $value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return self::jsonReadyValue($value->toArray());
        }

        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }

        return $value;
    }

    private static function isNativeJsonColumn(string $attribute): bool
    {
        $column = static::getTableSchema()->getColumn($attribute);
        return $column !== null && $column->type === \yii\db\Schema::TYPE_JSON;
    }

}
