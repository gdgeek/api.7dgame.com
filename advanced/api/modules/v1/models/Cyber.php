<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "cyber".
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property int|null $meta_id
 * @property string $create_at
 * @property string|null $updated_at
 * @property string|null $data
 * @property string|null $script
 *
 * @property User $author
 * @property Meta $meta
 * @property User $updater
 */
class Cyber extends \yii\db\ActiveRecord

{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_at', 'updated_at'],
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
        return 'cyber';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['author_id', 'updater_id', 'meta_id'], 'integer'],
            [['create_at', 'updated_at'], 'safe'],
            [['data', 'script'], 'string'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
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
            'meta_id' => 'Meta ID',
            'create_at' => 'Create At',
            'updated_at' => 'Updated At',
            'data' => 'Data',
            'script' => 'Script',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        unset($fields['author_id']);
        unset($fields['updater_id']);
        unset($fields['meta_id']);
        unset($fields['create_at']);
        unset($fields['updated_at']);

        return $fields;
    }

    public function extraFields()
    {
        return [
            'meta',
            'author',
        ];
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
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
    }

    /**
     * Gets query for [[Updater]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * {@inheritdoc}
     * @return CyberQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CyberQuery(get_called_class());
    }
}
