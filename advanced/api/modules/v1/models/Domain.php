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
 * @property array|null $links
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
            [['links'], 'safe'],
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
            'links' => Yii::t('app', 'Links'),
        ];
    }

    /**
     * 自动将 JSON 字段转为数组
     */
    public function afterFind()
    {
        parent::afterFind();
        if (is_string($this->links)) {
            $this->links = json_decode($this->links, true);
        }
    }

    /**
     * 保存前将数组转为 JSON 字符串
     */
    public function beforeSave($insert)
    {
        if (is_array($this->links)) {
            $this->links = json_encode($this->links, JSON_UNESCAPED_UNICODE);
        }
        return parent::beforeSave($insert);
    }
}
