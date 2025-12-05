<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property string $domain
 * @property string|null $title
 * @property string|null $author
 * @property string|null $description
 * @property string|null $keywords
 */
class Domain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain'], 'required'],
            [['domain', 'title', 'author', 'description', 'keywords'], 'string', 'max' => 255],
            [['domain'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'domain' => Yii::t('app', 'Domain'),
            'title' => Yii::t('app', 'Title'),
            'author' => Yii::t('app', 'Author'),
            'description' => Yii::t('app', 'Description'),
            'keywords' => Yii::t('app', 'Keywords'),
        ];
    }
}
