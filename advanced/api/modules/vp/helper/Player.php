<?php
namespace api\modules\vp\helper;

use api\modules\vp\models\Token;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use api\modules\vp\models\Level;

use api\modules\vp\models\Guide;
use api\modules\vp\models\Map;
class Player
{
  public $token = null;
  public function stars(){
    $player_id = $this->token->id;
    $stars = Level::find()->where(['player_id' => $player_id])->count('*');
    return $stars;
  }
  public function test(){
    return $this->token->token;
  }
  public function setup(){
    
    $all = Guide::find()
                ->orderBy(['order' => SORT_ASC]) // 或 SORT_DESC 进行降序排序
                ->all();
    for($i =0; $i*15<count($all); $i++){
      $map = Map::find()->where(['page' => $i])->one();
      if($map == null){
        $map = new Map();
        $map->page = $i;
      }
      if($map->validate()){
        $map->save();
        echo $map->id;
        $datas = array_slice($all, $i*15, 15);
        $begin = $i*15;
        $end = min($begin+15, count($all));
        for($n = $begin; $n<$end; $n++){
          $guide = $all[$n];
          $guide->map_id = $map->id;
          $guide->save();
        }
      }else{
        echo json_encode($map->errors);
      }
    }
  }
  function key(){
    
    $data = \Yii::$app->request->get();
    if(!isset($data['playerId']) || !isset($data['bundleId'])){
      return null;
    }
    $playerId =  urldecode($data['playerId']);
    $bundleId =  urldecode($data['bundleId']);
    $key = $playerId .'@'. $bundleId;
    return $key;
  }
}