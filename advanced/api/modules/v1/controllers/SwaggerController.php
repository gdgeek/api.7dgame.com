<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use OpenApi\Generator;

class SwaggerController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [],
                ],
            ],
        ];
    }

    /**
     * Output the OpenAPI specification JSON
     */
    public function actionJson()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $scanPaths = [
            Yii::getAlias('@api/modules/v1/Module.php'),
            Yii::getAlias('@api/modules/v1/controllers/EduClassController.php'),
            Yii::getAlias('@api/modules/v1/models/EduClass.php'),
        ];
        
        $openapi = Generator::scan($scanPaths);
        
        return json_decode($openapi->toJson());
    }
    
    /**
     * Output the OpenAPI specification YAML
     */
    public function actionYaml()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/x-yaml');
        
        $scanPaths = [
            Yii::getAlias('@api/modules/v1/controllers'),
            Yii::getAlias('@api/modules/v1/models'),
            Yii::getAlias('@api/modules/v1/Module.php'),
        ];
        
        // Filter out paths that don't exist
        $scanPaths = array_filter($scanPaths, function($path) {
            return file_exists($path);
        });

        $openapi = Generator::scan($scanPaths);
        
        return $openapi->toYaml();
    }
}
