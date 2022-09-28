<?php

namespace common\models;

use common\models\Programme;

use common\components\editor\Reader;
use common\models\EditorData;
use common\models\Logic;
use api\modules\v1\models\User;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $title
 * @property string $logic
 * @property string $configure
 * @property int|null $user_id
 * @property string|null $introduce
 * @property int|null $sharing
 * @property int|null $programme_id
 * @property int|null $image_id
 *
 * @property EditorData[] $editorDatas
 * @property File $image
 * @property User $user
 * @property Programme $programme
 * @property ScriptData $scriptData
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'logic', 'configure'], 'required'],
            [['logic', 'configure'], 'string'],
            [['user_id', 'sharing', 'programme_id', 'image_id'], 'integer'],
            [['title'], 'string', 'max' => 20],
            [['introduce'], 'string', 'max' => 140],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['programme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Programme::className(), 'targetAttribute' => ['programme_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'logic' => Yii::t('app', 'Logic'),
            'configure' => Yii::t('app', 'Configure'),
            'user_id' => Yii::t('app', 'User ID'),
            'introduce' => Yii::t('app', 'Introduce'),
            'sharing' => Yii::t('app', 'Sharing'),
            'programme_id' => Yii::t('app', 'Programme ID'),
            'image_id' => Yii::t('app', 'Image ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEditorDatas()
    {
        return $this->hasMany(EditorData::className(), ['project_id' => 'id']);
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
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

      /**
     * @return \yii\db\ActiveQuery
     */
    public function getName()
    {
        return $this->title;
    }
    /**
     * Gets query for [[Programme]].
     *
     * @return \yii\db\ActiveQuery|ProgrammeQuery
     */
    public function getProgramme()
    {
        return $this->hasOne(Programme::className(), ['id' => 'programme_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScriptData()
    {
        return $this->hasOne(ScriptData::className(), ['project_id' => 'id']);
    }
    /**
    * @return \yii\db\ActiveQuery
    * Gets query for [[Logics]].
    *
    * @return \yii\db\ActiveQuery|LogicQuery
    */
   public function getLogics()
   {
       return $this->hasMany(Logic::className(), ['project_id' => 'id']);
   }
    /**
     * {@inheritdoc}
     * @return ProjectQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProjectQuery(get_called_class());
    }
    public function extraProgramme(){

        if(!$this->programme){
            $programme = new Programme();
            $programme->save();
            $this->programme_id = $programme->id;
            $this->save();
            
        }
        if(empty($this->programme->configure)){

                $jsons = EditorData::findAll(['project_id' =>  $this->id]);
				$datas = array();
        
				foreach($jsons as $key=>$val){
					$data = new \stdClass();
					$data->type = $val->type;
					$data->node_id = $val->node_id;
					$data->data = json_decode($val->data);
					array_push($datas, $data);
				}
	
				$reader = new Reader();
                $this->programme->configure = json_encode($reader->reader($datas), JSON_UNESCAPED_SLASHES);
                //return $reader->reader($datas);
        }

        if(empty($this->programme->logic)){

            $this->programme->logic = $this->logic;
        }
        if(empty($this->programme->title)){

            $this->programme->title = $this->title;
        }
        if(empty($this->programme->information)){

            $this->programme->information = $this->introduce;
        }
        if(empty($this->programme->author_id)){

            $this->programme->author_id = $this->user_id;
        }
        unset($this->programme->id);

        unset($this->programme->author_id);


        return $this->programme;
    }
    function fields(){
        $fields = parent::fields();
        unset($fields['sharing']);
        unset($fields['programme_id']);
        unset($fields['logic']);
        unset($fields['configure']);
        unset($fields['title']);
        unset($fields['user_id']);
        unset($fields['introduce']);

        return $fields;
    }
    public function extraFields() 
    { 
        return [
                 'programme'=>function(){
                     return $this->extraProgramme();
                 },
                 
              ]; 
    } 

    public function afterDelete()
    {
        parent::afterDelete();
        $datas =$this->editorDatas;
        foreach($datas as $data){
            $data->delete();
        }
       
        $logics =$this->logics;
        foreach($logics as $logic){
            $logic->delete();
        }
       

    }
}
