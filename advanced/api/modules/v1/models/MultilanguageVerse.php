<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "multilanguage_verse".
 *
 * @property int $id
 * @property int $verse_id
 * @property string $language
 * @property string|null $name
 * @property string|null $description
 *
 * @property Verse $verse
 */
class MultilanguageVerse extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'multilanguage_verse';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'language'], 'required'],
            [['verse_id'], 'integer'],
            [['language'], 'string', 'max' => 256],
            [['name', 'description'], 'string', 'max' => 255],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
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
            'language' => 'Language',
            'name' => 'Name',
            'description' => 'Description',
        ];
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
     * @return MultilanguageVerseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MultilanguageVerseQuery(get_called_class());
    }
}
