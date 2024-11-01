<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_meta".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $meta_id
 *
 * @property Meta $meta
 * @property Verse $verse
 */
class VerseMeta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'meta_id'], 'required'],
            [['verse_id', 'meta_id'], 'integer'],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['verse_id', 'meta_id'], 'unique', 'targetAttribute' => ['verse_id', 'meta_id']],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verse_id' => 'Verse ID',
            'meta_id' => 'Meta ID',
        ];
    }

    /**
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
    }

    /**
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery|VerseQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }

    /**
     * {@inheritdoc}
     * @return VerseMetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseMetaQuery(get_called_class());
    }
}
