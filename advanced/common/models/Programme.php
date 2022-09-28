<?php

namespace common\models;

use Yii;
use api\modules\v1\models\User;

/**
 * This is the model class for table "programme".
 *
 * @property int $id
 * @property int|null $author_id
 * @property string|null $title
 * @property string|null $information
 * @property string|null $configure
 * @property string|null $logic
 *
 * @property Maker[] $makers
 * @property User $author
 */
class Programme extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'programme';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id'], 'integer'],
            [['information', 'configure', 'logic'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'author_id' => Yii::t('app', 'Author ID'),
            'title' => Yii::t('app', 'Title'),
            'information' => Yii::t('app', 'Information'),
            'configure' => Yii::t('app', 'Configure'),
            'logic' => Yii::t('app', 'Logic'),
        ];
    }

    /**
     * Gets query for [[Makers]].
     *
     * @return \yii\db\ActiveQuery|MakerQuery
     */
    public function getMakers()
    {
        return $this->hasMany(Maker::className(), ['programme_id' => 'id']);
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * {@inheritdoc}
     * @return ProgrammeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProgrammeQuery(get_called_class());
    }
}
