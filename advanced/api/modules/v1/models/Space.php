<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "space".
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property int $mesh_id
 * @property int|null $image_id
 * @property int|null $file_id
 * @property string $created_at
 * @property string|null $data
 *
 * @property User $user
 * @property File $mesh
 * @property File|null $image
 * @property File|null $file
 * @property VerseSpace[] $verseSpaces
 * @property Verse[] $verses
 */
class Space extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }

    public static function tableName()
    {
        return 'space';
    }

    public function rules()
    {
        return [
            [['name', 'mesh_id'], 'required'],
            [['user_id', 'mesh_id', 'image_id', 'file_id'], 'integer'],
            [['created_at', 'data'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['mesh_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['mesh_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['image_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'user_id' => Yii::t('app', 'User ID'),
            'mesh_id' => Yii::t('app', 'Mesh ID'),
            'image_id' => Yii::t('app', 'Image ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'data' => Yii::t('app', 'Data'),
        ];
    }

    public function beforeValidate()
    {
        $this->data = self::compactData($this->data, $this->image_id ? (int)$this->image_id : null);
        if (is_array($this->data) || is_object($this->data)) {
            $this->data = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return parent::beforeValidate();
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['data'] = function () {
            return self::compactData($this->data, $this->image_id ? (int)$this->image_id : null);
        };
        return $fields;
    }

    public static function compactData($value, ?int $thumbnailFileId = null)
    {
        $data = self::decodeDataValue($value);
        if ($data === null) {
            return $value;
        }

        if (($data['source'] ?? null) !== 'ar-slam-localization') {
            return $data;
        }

        $compact = [];
        foreach (['source', 'provider', 'zipMd5', 'zipName', 'thumbnailFileId'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $compact[$key] = $data[$key];
            }
        }

        if (!array_key_exists('thumbnailFileId', $compact) && $thumbnailFileId !== null) {
            $compact['thumbnailFileId'] = $thumbnailFileId;
        }

        return $compact;
    }

    private static function decodeDataValue($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            $decoded = json_decode(json_encode($value), true);
            return is_array($decoded) ? $decoded : null;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    public function extraFields()
    {
        return [
            'user',
            'mesh',
            'image',
            'file',
            'verses',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getMesh()
    {
        return $this->hasOne(File::class, ['id' => 'mesh_id']);
    }

    public function getImage()
    {
        return $this->hasOne(File::class, ['id' => 'image_id']);
    }

    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    public function getVerseSpaces()
    {
        return $this->hasMany(VerseSpace::class, ['space_id' => 'id']);
    }

    public function getVerses()
    {
        return $this->hasMany(Verse::class, ['id' => 'verse_id'])->via('verseSpaces');
    }
}
