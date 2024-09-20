<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

use mdm\admin\models\Assignment;

use common\models\Maker;

use yii\web\NotFoundHttpException;
use api\modules\v1\models\Resource;
use api\modules\v1\models\User;
/**
* Site controller
*/
class MakerController extends Controller
{
    public $enableCsrfValidation = false;
    
    public $layout = '@backend/views/layouts/maker/layout.php';
    
    public function actionIndex()
    {
        
        $post = Yii::$app->request->post();
        $ret = new \stdClass();
        if($this->login($post['username'], $post['password'])){
            $data = json_decode($post['data']);
            
            //��data ���� maker ���档
            // $data->polygen =  
            $ret->succeed = true;
        }else{
            $ret->succeed = false;
        }
        return json_encode($this->login($post['username'], $post['password']));
        return json_encode($post);
        $data = json_decode($post['data']);
        return json_encode($data);
    }
    
    private function setup(){
        $post = Yii::$app->request->post();
        if(!isset($post['username'])){
            $post = Yii::$app->request->get();
        }
        
        
        
        $ret = new \stdClass();
        
        if($this->login($post['username'], $post['password'])){
            $ret->succeed = true;
        }else{
            $ret->error = "user no!";
            $ret->succeed = false;
        }
        return [$ret, json_decode($post['data'])];
    }
    
    public function polygenData($polygen){
        $data = new \stdClass();
        $data->id = "polygen";
        $data->mesh = new \stdClass();
        $data->mesh->file = new \stdClass();
        
        if(!isset($polygen)){
            return;
        }
        
        $data->id = "polygen";
        
        
        
        $f = $polygen->file;
        
        $file = new \stdClass();
        if($f != null){
            $pattern = '/(http[s]?:\/\/[^\/]+\/)(.+)/';
            preg_match($pattern, $f->url,$match);
            $file->name = $match[2];
            $file->url = $match[1];
            
        }else{
            
            $file->name = $polygen->file_name .'.'. $polygen->type;
            $file->url = $polygen->url;
            
        }
        
        $file->cache = true;
        $file->compress = false;
        
        if(strpos($polygen->type, '.zip') !== false){
            
            $file->compress = true;
            $file->compressed = "zip";
        }else{
            
            $file->compress = false;
        }
        
        $data->mesh->type ="fbx";
        $data->mesh->file = $file;
        $data->transform = new \stdClass();
        $data->transform->position =["x"=>"0", "y"=>"0", "z"=>"0"];
        $data->transform->scale  =["x"=>"1", "y"=>"1", "z"=>"1"];
        $data->transform->angle  =["x"=>"0", "y"=>"0", "z"=>"0"];
        
        
        return $data;
    }
    
    public function actionUpload(){
        list($ret, $data) = $this->setup();
        if(!$ret->succeed){
            return json_encode($ret);
        }
        if(isset($data->projectId)){
            $maker = $this->findMaker($data->projectId);
            if($maker != null){
                $maker->data = json_encode($data);
                $maker->config = null;
                $maker->logic = null;
                // $ret->snapshots = $maker->snapshots;
                
                if($maker->validate()){
                    $maker->save();
                }else{
                    $ret->succeed = false;
                    $ret->error = json_encode($maker->getErrors(), JSON_UNESCAPED_UNICODE);
                }
                $result = new \stdClass();
                $result->succeed = true;
                $ret->result = json_encode($result);
                $ret->data = $data;
                $ret->succeed = true;
                
            }else{
                
                $ret->error = "marker no!";
                $ret->succeed = false;
            }
        }else{
            
            $ret->error = "projectId no!";
            $ret->succeed = false;
        }
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }
    public function result($maker){
        
        
        $polygen = $maker->getPolygen()->one();
        
        
        $ret = new \stdClass();
        $ret->projectId = $maker->id;
        $ret->polygen =  $this->polygenData($polygen);
        $ret->data = json_decode($maker->data);
        return json_encode($ret, JSON_UNESCAPED_SLASHES);
    }
    public function actionLogin(){
        
        list($ret, $data) = $this->setup();
        if(!$ret->succeed){
            return json_encode($ret);
        }
        
        if(isset($data->projectId)){
            $maker = $this->findMaker($data->projectId);
            if($maker != null){
                
                $ret->result = $this->result($maker);
                if($ret->result != null){
                    return json_encode($ret, JSON_UNESCAPED_SLASHES);
                    //return;
                }else{
                    
                    $ret->error = "result no!";
                }
            }else{
                
                $ret->error = "maker no!";
            }
        }
        $ret->succeed = false;
        
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }
    public function actionTest(){
        $maker = $this->findMaker(63);
        return json_encode($this->result($maker));
    }
    public function actionRegister(){
        list($ret, $data) = $this->setup(); 
        if(!$ret->succeed){
            return json_encode($ret, JSON_UNESCAPED_SLASHES);
        }
        //$ret->data = $data;
        
        if(isset($data->polygenId)){
            $polygen = $this->findPolygen($data->polygenId);
            if($polygen != null){
                $maker = new Maker();
                $maker->user_id = Yii::$app->user->id;
                
                $maker->polygen_id = $polygen->id;
                if($maker->validate()){
                    $maker->save();
                    
                    $ret->result =  $this->result($maker);// gzencode($this->result($maker));
                    
                    if($ret->result != null){
                        $ret->succeed = true;
                        return json_encode($ret, JSON_UNESCAPED_SLASHES);
                    }else{
                        $ret->succeed = false;
                        $ret->error = "result no!";
                    }
                    
                }else{
                    $ret->succeed = false;
                    $ret->error = json_encode($maker->getErrors(), JSON_UNESCAPED_UNICODE );
                    
                }
                
            }else{
                
                $ret->succeed = false;
                $ret->error = "polygen no!";
            }
        }else{
            
            $ret->succeed = false;
            $ret->error = "polygenId no!";
        }
        return json_encode($ret, JSON_UNESCAPED_UNICODE );
    }
    protected function findPolygen($id){
        
        if (($model = Resource::find()->where(['id' => $id, 'type' => 'polygen'])->one()) !== null) {
            return $model;
        }
        
        return null;
        
    }
    protected function findMaker($id){
        if (($model = Maker::findOne($id)) !== null) {
            return $model;
        }
        
        return null;
        //throw new NotFoundHttpException('The requested page does not exist.');
        
    }
    protected function login($username, $password){
        
        $user = User::findByUsername($username);
        if (!$user || !$user->validatePassword($password)) {
            return false;
        }
        return Yii::$app->user->login($user, 0);
    }
    
    
}
