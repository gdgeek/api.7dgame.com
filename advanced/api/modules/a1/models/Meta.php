<?php

namespace api\modules\a1\models;

use api\modules\v1\models\Cyber;
use api\modules\v1\models\File;
use api\modules\v1\models\MetaQuery;
use api\modules\v1\models\User;
use api\modules\v1\models\MetaCode;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use api\modules\v1\components\Validator\JsonValidator;
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
* @property Cyber[] $cybers
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
            [['info', 'data', 'events'], JsonValidator::class],
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
        //unset($fields['custom']);
        $fields['type'] = function ($model) {
            return $model->prefab == 0 ? 'sample' : 'module';
        };
        //unset($fields['id']);
        $fields['data'] = function () {
            
            if(!is_string($this->data) && !is_null($this->data)){
                return json_encode($this->data);
            }
            return $this->data;
        };
        $fields['events'] = function () {
            if(!is_string($this->events)&& !is_null($this->events)){
                return json_encode($this->events);
            }
            return $this->events;
        };
        $fields['script'] = function () {
            
            $metaCode = $this->metaCode;
            if($metaCode && $metaCode->code){
                return $metaCode->code->lua;
            }
            
            if ($this->cyber && $this->cyber->script) {
                return $this->cyber->script;
            }
            return 'local meta = {}';
            
        };
        return $fields;
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
    * Gets query for [[Cybers]].
    *
    * @return \yii\db\ActiveQuery|CyberQuery
    */
    public function getCyber()
    {
        return $this->hasOne(Cyber::className(), ['meta_id' => 'id']);
    }
    
    /**
    * Gets query for [[MetaRetes]].
    *
    * @return \yii\db\ActiveQuery|MetaReteQuery
    */
    public function getMetaRetes()
    {
        return $this->hasMany(MetaRete::className(), ['meta_id' => 'id']);
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
        
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        $resourceIds = \api\modules\v1\helper\Meta2Resources::Handle($data);
        return $resourceIds;
    }
    public function extraResources()
    {
        $resourceIds = $this->resourceIds;
        $items = Resource::find()->where(['id' => $resourceIds])->all();
        return $items;
    }
    
    public function extraEditor()
    {
        $editor = \api\modules\v1\helper\Meta2Editor::Handle($this);
        return $editor;
    }
    
    public function upgrade($data)
    {
        $ret = false;
        if (isset($data->parameters) && isset($data->parameters->transfrom)) {
            
            $ret = true;
            $data->parameters->transform = $data->parameters->transfrom;
            unset($data->parameters->transfrom);
        }
        
        if (isset($data->chieldren)) {
            
            $ret = true;
            $data->children = $data->chieldren;
            unset($data->chieldren);
        }
        if (isset($data->children->entities)) {
            foreach ($data->children->entities as $entity) {
                if ($this->upgrade($entity)) {
                    $ret = true;
                    
                }
            }
        }
        if (isset($data->children->addons)) {
            foreach ($data->children->addons as $addon) {
                //   echo 123;
                if ($this->upgrade($addon)) {
                    $ret = true;
                }
            }
        }
        if (isset($data->children->components)) {
            foreach ($data->children->components as $component) {
                if ($this->upgrade($component)) {
                    $ret = true;
                }
            }
        }
        
        return $ret;
    }
    public function afterFind()
    {
        
        parent::afterFind();
        if(is_string($this->data)){
            $data = json_decode($this->data);
        }else{
            $data =json_decode(json_encode($this->data));
        }
        $change = $this->upgrade($data);
        if ($change) {
            $this->data = json_encode($data);
            $this->save();
        }
        
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
