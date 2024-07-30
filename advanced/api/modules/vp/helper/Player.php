<?php
namespace api\modules\vp\helper;

use api\modules\vp\models\Token;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use api\modules\vp\models\Level;

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