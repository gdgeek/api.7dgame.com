<?php

namespace api\modules\v1\controllers;
//namespace api\controllers;

use api\modules\v1\models\data\Login;
use api\modules\v1\models\data\Link;
use api\modules\v1\models\data\Register;
use api\modules\v1\models\User;
use api\modules\v1\models\AppleId;
use common\components\security\RateLimitBehavior;
use Yii;
use yii\helpers\Url;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="V1Site",
 *     description="V1 站点接口"
 * )
 */
class SiteController extends \yii\rest\Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['rateLimiter'] = [
            'class' => RateLimitBehavior::class,
            'rateLimiter' => 'rateLimiter',
            'defaultStrategy' => 'ip',
            'actionStrategies' => [
                'login' => 'login',
            ],
        ];

        return $behaviors;
    }




    private function getAppleUser($code, $appleParameter)
    {


        \Firebase\JWT\JWT::$leeway = 60;

        $provider = new \League\OAuth2\Client\Provider\Apple($appleParameter);

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        $data = $provider->getResourceOwner($token);
        return [
            "id" => $data->getId(),
            "email" => $data->getEmail(),
            "first_name" => $data->getFirstName(),
            "last_name" => $data->getLastName(),
            "token" => $token->getToken(),
            "privateEmail" => $data->isPrivateEmail(),
            // "array" => $data->toArray() 
        ];

    }

    /**
     * @OA\Post(
     *     path="/v1/site/apple-id",
     *     summary="Apple ID 认证",
     *     description="使用 Apple ID 进行认证",
     *     tags={"V1Site"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data", "url", "key"},
     *             @OA\Property(property="data", type="object", description="Apple 认证数据"),
     *             @OA\Property(property="url", type="string", description="回调 URL"),
     *             @OA\Property(property="key", type="string", description="密钥标识")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="认证成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="apple_id", type="string", description="Apple ID"),
     *             @OA\Property(property="email", type="string", description="邮箱"),
     *             @OA\Property(property="user_id", type="integer", description="关联的用户ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=404, description="参数缺失")
     * )
     */
    public function actionAppleId()
    {



        $post = Yii::$app->request->post();

        if (!isset($post['data'])) {
            throw new \yii\web\NotFoundHttpException('data');
        }
        if (!isset($post['url'])) {
            throw new \yii\web\NotFoundHttpException('url');
        }
        if (!isset($post['key'])) {
            throw new \yii\web\NotFoundHttpException('key');
        }
        $key = $post['key'];
        $data = $post['data'];
        $url = $post['url'];

        if (!isset($data['userData']) || !isset($data['userData']['aud'])) {
            throw new \yii\web\NotFoundHttpException('userData.aud');
        }
        $aud = $data['userData']['aud'];

        if (!isset($data['authorization'])) {
            throw new \yii\web\NotFoundHttpException('authorization');
        }
        if (!isset($data['authorization']['code'])) {
            throw new \yii\web\NotFoundHttpException('code');
        }
        $appleParameter = [
            'clientId' => $aud, // com.mrpp.www //客户端提供
            'teamId' => getenv('APPLE_TEAM_ID'), // PK435YWZ25 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId' => getenv($key), // UZJ8VJVX7K https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath' => str_replace('{KEY_ID}', getenv($key), getenv('APPLE_AUTH_KEY')), // __DIR__ . '/AuthKey_UZJ8VJVX7K.p8' -> Download key above
            'redirectUri' => $url,
            'scope' => "email name",
        ];


        $code = $data['authorization']['code'];
        $result = $this->getAppleUser($code, $appleParameter);
        $aid = AppleId::find()->where(['apple_id' => $result['id']])->one();
        if ($aid && $aid->user) {
            //     $aid->user->addRoles(['mrpp.com']);
            return $aid;
        }

        if (!$aid) {
            $aid = new AppleId();
            $aid->apple_id = $result['id'];
            $aid->email = $result['email'];
            $aid->first_name = $result['first_name'];
            $aid->last_name = $result['last_name'];
            $user = User::find()->where(['email' => $result['email']])->one();
            if ($user) {
                $aid->user_id = $user->id;
                if ($aid->validate()) {
                    //  $user->addRoles(['mrpp.com']);
                    $aid->save();
                }
            } else {
                $aid->token = $result['token'];
                if ($aid->validate()) {
                    $aid->save();
                } else {
                    throw new Exception(json_encode($aid->errors));
                }
            }
            ////   if($aid->user){
            //      $aid->user->addRoles(['mrpp.com']);
            //   }
            return $aid;
        } else {
            $aid->token = $result['token'];
            if ($aid->validate()) {
                $aid->save();
            } else {
                throw new Exception(json_encode($aid->errors));
            }
            // if($aid->user){
            //    $aid->user->addRoles(['mrpp.com']);
            // }
            return $aid;
        }

    }
    /**
     * @OA\Get(
     *     path="/v1/site/test",
     *     summary="测试接口",
     *     description="用于测试的接口",
     *     tags={"V1Site"},
     *     @OA\Response(
     *         response=200,
     *         description="测试结果",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="快照ID"),
     *             @OA\Property(property="uuid", type="string", description="快照UUID"),
     *             @OA\Property(property="name", type="string", description="快照名称")
     *         )
     *     )
     * )
     */
    public function actionTest()
    {
        $id = 408;
        $verse = \api\modules\v1\models\VerseSnapshot::findOne($id);
        $snapshot = \api\modules\v1\models\Snapshot::CreateById($id);
        $snapshot->save();

         
        return $snapshot->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid']);
      
    }

    /**
     * @OA\Post(
     *     path="/v1/site/apple-id-create",
     *     summary="创建 Apple ID 账号",
     *     description="使用 Apple ID 创建新账号",
     *     tags={"V1Site"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"apple_id", "token", "username", "password"},
     *             @OA\Property(property="apple_id", type="string", description="Apple ID"),
     *             @OA\Property(property="token", type="string", description="临时 Token"),
     *             @OA\Property(property="username", type="string", description="用户名"),
     *             @OA\Property(property="password", type="string", description="密码")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="apple_id", type="string", description="Apple ID"),
     *             @OA\Property(property="user_id", type="integer", description="用户ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误")
     * )
     */
    public function actionAppleIdCreate()
    {
        $register = new Register();
        $appleId = Yii::$app->request->post('apple_id');
        if (!$appleId) {
            throw new Exception(json_encode(Yii::$app->request->post()), 400);
        }

        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }
        $aid = AppleId::find()->where(['apple_id' => $appleId])->one();
        if (!$aid) {
            throw new Exception("No Apple Id", 400);
        }
        if ($aid->user !== null) {

            $aid->token = null;
            if ($aid->validate()) {
                $aid->save();
            }
            //   if($aid->user){
            //       $aid->user->addRoles(['mrpp.com']);
            //  }
            return $aid;
        }

        if ($register->load(Yii::$app->getRequest()->getBodyParams(), '')) {

            $nickname = null;
            if ($aid->first_name) {
                $nickname = $aid->first_name;
            }
            if ($nickname && $aid->last_name) {
                $nickname += ' ';
            }
            if ($aid->last_name) {
                $nickname += $aid->last_name;
            }
            if ($register->create($aid->email, $nickname)) {

                $aid->user_id = $register->user->id;

                $aid->token = null;
                if ($aid->validate()) {
                    $aid->save();
                } else {
                    $register->remove();
                    throw new Exception(json_encode($aid->errors), 400);
                }
                //   if($aid->user){
                //       $aid->user->addRoles(['mrpp.com']);
                //   }
                return $aid;
            } else {
                throw new Exception("error!", 400);
            }
        } else {
            throw new Exception(json_encode($register->getFirstErrors()), 400);
        }

    }


    /**
     * @OA\Post(
     *     path="/v1/site/apple-id-link",
     *     summary="关联 Apple ID",
     *     description="将 Apple ID 关联到现有账号",
     *     tags={"V1Site"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"apple_id", "token", "username", "password"},
     *             @OA\Property(property="apple_id", type="string", description="Apple ID"),
     *             @OA\Property(property="token", type="string", description="临时 Token"),
     *             @OA\Property(property="username", type="string", description="用户名"),
     *             @OA\Property(property="password", type="string", description="密码")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="关联成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="apple_id", type="string", description="Apple ID"),
     *             @OA\Property(property="user_id", type="integer", description="用户ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误")
     * )
     */
    public function actionAppleIdLink()
    {
        $link = new Link();
        $appleId = Yii::$app->request->post('apple_id');
        if (!$appleId) {
            throw new Exception(("No AppleId"), 400);
        }
        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }

        $aid = AppleId::find()->where(['apple_id' => $appleId, 'token' => $token])->one();
        if (!$aid) {
            throw new Exception("No AppleId", 400);
        }
        if ($aid->user !== null) {
            $aid->token = null;
            $aid->save();
            // $aid->user->addRoles(['mrpp.com']);
            return $aid;
        }
        if ($link->load(Yii::$app->getRequest()->getBodyParams(), '')) {

            if ($link->bind()) {
                $aid->user_id = $link->user->id;

                $aid->token = null;
                if ($aid->validate()) {
                    //  $link->user->addRoles(['mrpp.com']);
                    $aid->save();
                } else {
                    throw new Exception(json_encode($aid->errors), 400);
                }
                // $aid->user->addRoles(['mrpp.com']);
                return $aid;
            } else {
                throw new Exception('Error', 400);
            }
        } else {
            throw new Exception(json_encode($link->getFirstErrors()), 400);
        }
    }
    /**
     * @OA\Post(
     *     path="/v1/site/login",
     *     summary="用户登录",
     *     description="使用用户名和密码登录",
     *     tags={"V1Site"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", description="用户名", example="admin"),
     *             @OA\Property(property="password", type="string", description="密码", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="用户ID"),
     *             @OA\Property(property="username", type="string", description="用户名"),
     *             @OA\Property(property="auth", type="object", description="认证信息")
     *         )
     *     ),
     *     @OA\Response(response=400, description="登录失败")
     * )
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {

        $login = new Login();
        if ($login->load(Yii::$app->getRequest()->getBodyParams(), '') && $login->login()) {
            return $login->user->toArray([], ['auth']);
        } else {
            throw new Exception(json_encode($login->getFirstErrors()), 400);
        }
    }

}
