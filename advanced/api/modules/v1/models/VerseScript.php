<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "verse_script".
 *
 * @property int $id
 * @property string|null $created_at
 * @property int $verse_id
 * @property string|null $script
 * @property string $title
 * @property string|null $blockly
 * @property string $uuid
 *
 * @property Verse $verse
 */
class VerseScript extends \yii\db\ActiveRecord

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_script';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['verse_id', 'title', 'uuid'], 'required'],
            [['verse_id'], 'integer'],
            [['script', 'blockly'], 'string'],
            [['title', 'uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }
    public function extraFields()
    {
        return [
            'verse',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'verse_id' => 'Verse ID',
            'script' => 'Script',
            'title' => 'Title',
            'blockly' => 'Blockly',
            'uuid' => 'Uuid',
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
     * @return VerseScriptQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseScriptQuery(get_called_class());
    }
}
