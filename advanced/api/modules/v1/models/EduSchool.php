<?php

namespace api\modules\v1\models;

use api\modules\v1\components\SchoolPrincipalRule;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Yii;

/**
 * This is the model class for table "edu_school".
 *
 * @property int $id
 * @property string|null $name
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $image_id
 * @property string|null $info
 * @property int|null $principal_id
 *
 * @property EduClass[] $eduClasses
 * @property File $image
 * @property User $principal
 */
class EduSchool extends \yii\db\ActiveRecord
{



    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                        \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                        \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],

                    ],
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * 保存后清除校长缓存
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // 清除学校 principal_id 缓存
        SchoolPrincipalRule::clearSchoolCache($this->id);

        // 兼容历史：若仍存在按 userId 的缓存键，一并清理
        if ($this->principal_id) {
            SchoolPrincipalRule::clearCache($this->principal_id);
        }
        if (!$insert && isset($changedAttributes['principal_id']) && $changedAttributes['principal_id']) {
            SchoolPrincipalRule::clearCache($changedAttributes['principal_id']);
        }
    }

    /**
     * 删除后清除校长缓存
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // 清除学校 principal_id 缓存
        SchoolPrincipalRule::clearSchoolCache($this->id);

        // 兼容历史：若仍存在按 userId 的缓存键，一并清理
        if ($this->principal_id) {
            SchoolPrincipalRule::clearCache($this->principal_id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'info'], 'safe'],
            [['image_id', 'principal_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['principal_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['principal_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'image_id' => Yii::t('app', 'Image ID'),
            'info' => Yii::t('app', 'Info'),
            'principal_id' => Yii::t('app', 'Principal ID'),
        ];
    }

    /**
     * Gets query for [[EduClasses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduClasses()
    {
        return $this->hasMany(EduClass::className(), ['school_id' => 'id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }
    public function filelds()
    {
        return ['id', 'name', 'info'];
    }
    public function extraFields()
    {
        return ['image', 'eduClasses', 'principal','classes'];
    }
    public function getClasses()
    {
        return $this->hasMany(EduClass::className(), ['school_id' => 'id']);
    }
    /**
     * Gets query for [[Principal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrincipal()
    {
        return $this->hasOne(User::className(), ['id' => 'principal_id']);
    }
}
