<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meta_version".
 *
 * @property int $id
 * @property int $meta_id
 * @property int $version_id
 *
 * @property Verse $meta
 * @property Version $version
 */
class MetaVersion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta_version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta_id', 'version_id'], 'required'],
            [['meta_id', 'version_id'], 'integer'],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['meta_id' => 'id']],
            [['version_id'], 'exist', 'skipOnError' => true, 'targetClass' => Version::className(), 'targetAttribute' => ['version_id' => 'id']],
        ];
    }
    public static function upgrade($meta){
        
        $current = Version::getCurrentVersionNumber();
        $number = isset($meta->version) ? $meta->version->number : 0;

        if ($number == $current) {
            return;
        }
        if (empty($meta->uuid)) {
            $meta->uuid = \Faker\Provider\Uuid::uuid();
        }

        $version = Version::findOne(['number' => $current]);
        if (!$version) {
            return;
        }

        $vv = self::findOne(['meta_id' => $meta->id]);

        if (!$vv) {
            $vv = new self();
            $vv->meta_id = $meta->id;
        }

        $meta->save();
        $vv->version_id = $version->id;
        $vv->save();

        
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'meta_id' => Yii::t('app', 'Meta ID'),
            'version_id' => Yii::t('app', 'Version ID'),
        ];
    }

    /**
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Verse::className(), ['id' => 'meta_id']);
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
