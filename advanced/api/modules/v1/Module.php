<?php
namespace api\modules\v1;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="API documentation for V1 module"
 * )
 * @OA\Server(
 *     url="/v1",
 *     description="V1 API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\PathItem(path="/")
 */
class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

       // $this->params['foo'] = 'bar';
        // ...  其他初始化代码 ...
    }
}