<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_version".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $version_id
 *
 * @property Verse $verse
 * @property Version $version
 */
class VerseVersion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'version_id'], 'required'],
            [['verse_id', 'version_id'], 'integer'],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['version_id'], 'exist', 'skipOnError' => true, 'targetClass' => Version::className(), 'targetAttribute' => ['version_id' => 'id']],
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
            'version_id' => Yii::t('app', 'Version ID'),
        ];
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

    public static function upgrade($verse)
    {

        $current = 1;
        $number = isset($verse->version) ? $verse->version->number : 0;

        if ($number == $current) {
            return;
        }
        if (empty($verse->uuid)) {
            $verse->uuid = \Faker\Provider\Uuid::uuid();
        }

        $version = Version::findOne(['number' => $current]);
        if (!$version) {
            return;
        }

        $vv = self::findOne(['verse_id' => $verse->id]);

        if (!$vv) {
            $vv = new self();
            $vv->verse_id = $verse->id;
        }
        $verse->save();
        $metas = $verse->metas;
        $vv->version_id = $version->id;
        $vv->save();
    }
    /**
     * Gets query for [[Version]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVersion()
    {
        return $this->hasOne(Version::className(), ['id' => 'version_id']);
    }
}
