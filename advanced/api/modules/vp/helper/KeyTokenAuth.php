<?php
namespace api\modules\vp\helper;

use api\modules\vp\models\VpToken;
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
      if(isset($data['playerId']) && isset($data['bundleId'])){
        $playerId =  urldecode($data['playerId']);
        $bundleId =  urldecode($data['bundleId']);
        $tk = urldecode($data['token']);
        $key = $playerId .'@'. $bundleId;
        $token = VpToken::find()->where(['key' => $key, 'token'=>$tk])->one();
        if($token != null){
          return true;
        }
      }
     
      return null;
    }

    /*
    protected function validateKeyToken($key, $token)
    {
        // 在数据库中查找 key 和 token
        $record = Yii::$app->db->createCommand('SELECT * FROM api_keys WHERE api_key=:key AND api_token=:token')
            ->bindValue(':key', $key)
            ->bindValue(':token', $token)
            ->queryOne();

        return $record !== false;
    }*/
}