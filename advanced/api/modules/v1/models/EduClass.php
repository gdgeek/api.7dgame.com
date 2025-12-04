<?php

namespace api\modules\v1\models;

use Yii;
use OpenApi\Annotations as OA;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "edu_class".
 *
 * @OA\Schema(
 *     schema="EduClass",
 *     title="EduClass Model",
 *     description="班级模型",
 *     @OA\Property(property="id", type="integer", description="ID"),
 *     @OA\Property(property="name", type="string", description="班级名称"),
 *     @OA\Property(property="school_id", type="integer", description="学校ID"),
 *     @OA\Property(property="image_id", type="integer", description="图片ID"),
 *     @OA\Property(property="info", type="string", description="信息"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="创建时间"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新时间")
 * )
 *
 * @property int $id
 * @property string|null $name
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $school_id
 * @property int|null $image_id
 * @property string|null $info
 *
 * @property File $image
 * @property EduSchool $school
 * @property EduStudent[] $eduStudents
 * @property EduTeacher[] $eduTeachers
 */
class EduClass extends \yii\db\ActiveRecord
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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edu_class';
    }

    public function fields()
    {
        return ['id', 'name', 'created_at', 'updated_at', 'info','school_id'];
    }
    public function extraFields()
    {
        return ['image', 'school', 'eduStudents', 'eduTeachers', 'students', 'teachers'];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['school_id'], 'required'],
            [['created_at', 'updated_at', 'info'], 'safe'],
            [['school_id', 'image_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['school_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduSchool::className(), 'targetAttribute' => ['school_id' => 'id']],
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
            'school_id' => Yii::t('app', 'School ID'),
            'image_id' => Yii::t('app', 'Image ID'),
            'info' => Yii::t('app', 'Info'),
        ];
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

    /**
     * Gets query for [[School]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSchool()
    {
        return $this->hasOne(EduSchool::className(), ['id' => 'school_id']);
    }

    /**
     * Gets query for [[EduStudents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduStudents()
    {
        return $this->hasMany(EduStudent::className(), ['class_id' => 'id']);
    }
     /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStudents()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('edu_student', ['class_id' => 'id']);
    }
    /**
     * Gets query for [[EduTeachers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduTeachers()
    {
        return $this->hasMany(EduTeacher::className(), ['class_id' => 'id']);
    }
       /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTeachers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('edu_teacher', ['class_id' => 'id']);
    }   
}
