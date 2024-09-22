<?php

namespace api\modules\v1\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Yii;
use Random\Randomizer;
/**
* This is the model class for table "verse_release".
*
* @property int $id
* @property string $code
* @property string $created_at
* @property string $updated_at
* @property int $lifetime
* @property int $verse_id
*
* @property Verse $verse
*/
class VerseRelease extends \yii\db\ActiveRecord
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
            return 'verse_release';
        }
        
        /**
        * {@inheritdoc}
        */
        public function rules()
        {
            return [
                [['code', 'verse_id'], 'required'],
                [['created_at', 'updated_at'], 'safe'],
                [['lifetime', 'verse_id'], 'integer'],
                [['code'], 'string', 'max' => 255],
                [['code'], 'unique'],
                [['verse_id'], 'unique'],
                [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            ];
        }
        
        
        public function beforeValidate()
        {
            
            
            
            if(!$this->code){
                $randomizer = new Randomizer();
                $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                do {
                    $this->code = $randomizer->getBytesFromString($characters, 6);
                    $model = VerseRelease::findOne(['code' => $this->code]);
                } while ($model !== null);    
            }
            
            
            // 调用父类的 beforeValidate 方法
            if (!parent::beforeValidate()) {
                return false;
            }
            return true;
        }
        
        /**
        * {@inheritdoc}
        */
        public function attributeLabels()
        {
            return [
                'id' => 'ID',
                'code' => 'Code',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'lifetime' => 'Lifetime',
                'verse_id' => 'Verse ID',
            ];
        }
        
        /**
        * Gets query for [[Verse]].
        *
        * @return \yii\db\ActiveQuery
        */
        public function getVerse()
        {
            return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
        }
    }
    