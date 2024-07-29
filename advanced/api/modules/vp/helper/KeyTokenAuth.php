<?php
namespace api\modules\vp\helper;

use api\modules\vp\models\Token;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class KeyTokenAuth extends AuthMethod
{
    
    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
      $data = \Yii::$app->request->get();
      if(isset($data['playerId']) && isset($data['bundleId']) && isset($data['token'])){
        $playerId =  urldecode($data['playerId']);
        $bundleId =  urldecode($data['bundleId']);
        $tk = urldecode($data['token']);
        $key = $playerId .'@'. $bundleId;
        $token = Token::find()->where(['key' => $key, 'token'=>$tk])->one();
        if($token != null){
          \Yii::$app->player->token = $token;
          return true;
        }
      }
     
      return null;
    }


}