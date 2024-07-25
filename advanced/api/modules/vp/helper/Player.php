<?php
namespace api\modules\vp\helper;

use api\modules\vp\models\VpToken;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class Player
{
  public $token = null;
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