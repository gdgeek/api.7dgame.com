<?php

namespace api\modules\private\models;

use api\modules\private\models\File;
use api\modules\private\models\MetaQuery;
use api\modules\private\models\User;
use api\modules\private\models\MetaCode;
use api\modules\v1\helper\Meta2Resources;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
* This is the model class for table "meta".
*
* @property int $id
* @property int $author_id
* @property int|null $updater_id
* @property string $created_at
* @property string $updated_at
* @property string|null $info
* @property int|null $image_id
* @property string|null $data
* @property string|null $uuid
*
* @property User $author
* @property File $image
* @property User $updater
* @property MetaRete[] $metaRetes
*/
class Meta extends \yii\db\ActiveRecord

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
        return 'meta';
    }
    
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['author_id', 'updater_id', 'image_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info', 'data', 'events'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }
    
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['author_id']);
        unset($fields['updater_id']);
        unset($fields['updated_at']);
        unset($fields['created_at']);
        unset($fields['image_id']);
        unset($fields['info']);
        $fields['type'] = function ($model) {
            return $model->prefab == 0 ? 'entity' : 'prefab';
        };
       $fields['data'] = function () {
            return $this->data;
        };
       
        $fields['code'] = function () { return $this->getCode(); };
        /* */
        return $fields;
    }
  
    public function getCode(){

        $metaCode = $this->metaCode;
        $cl = Yii::$app->request->get('cl');
        if(!$cl){
            $cl = 'lua';
        }
        if($metaCode && $metaCode->code){
            $script = $metaCode->code->$cl;
        }
        
        if($cl == 'lua'){
            $substring = "local meta = {}\nlocal index = ''\n";
        }else{
            $substring = '';
        }
   

        if(isset($script)){
            if (strpos($script, $substring) !== false) {
                return $script;
            } else {
                return $substring.$script;
            }
        }else{
            return $substring;
        }  
      
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'info' => 'Info',
            'image_id' => 'Image ID',
            'data' => 'Data',
            'uuid' => 'Uuid',
        ];
    }
    
    /**
    * Gets query for [[Author]].
    *
    * @return \yii\db\ActiveQuery|UserQuery
    */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    
    /**
    * Gets query for [[Updater]].
    *
    * @return \yii\db\ActiveQuery|UserQuery
    */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }
    
    public function getMetaCode()
    {
        return $this->hasOne(MetaCode::className(), ['meta_id' => 'id']);
    }
    
    
  
    /**
    * Gets query for [[Image]].
    *
    * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
    */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }
    
    public function getResourceIds()
    {
      
        return Meta2Resources::Handle($this->data);
  
    }
    public function extraResources()
    {
        $ids = $this->getResourceIds();
        return Resource::find()->where(['id' => $ids]);
    }
    
    public function extraEditor()
    {
        $editor = \api\modules\v1\helper\Meta2Editor::Handle($this);
        return $editor;
    }
   
    /**
    * {@inheritdoc}
    * @return MetaQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new MetaQuery(get_called_class());
    }
}
