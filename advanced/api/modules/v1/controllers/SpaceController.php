<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Space;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class SpaceController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Space';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Space::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['id' => SORT_DESC]),
        ]);
    }

    public function actionCreate()
    {
        $body = Yii::$app->request->bodyParams;
        $existingSpace = $this->findExistingArSlamSpace($body);
        if ($existingSpace) {
            Yii::$app->response->statusCode = 200;
            return $existingSpace;
        }

        $model = new Space();
        $model->load($body, '');
        $this->checkAccess('create', $model);

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        if (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        Yii::$app->response->statusCode = 422;
        return $model;
    }

    private function findExistingArSlamSpace(array $body): ?Space
    {
        $incomingData = $this->decodeSpaceData($body['data'] ?? null);
        $zipMd5 = trim((string)($incomingData['zipMd5'] ?? ''));
        if (($incomingData['source'] ?? null) !== 'ar-slam-localization' || $zipMd5 === '') {
            return null;
        }

        $spaces = Space::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        foreach ($spaces as $space) {
            $spaceData = $this->decodeSpaceData($space->data);
            if (
                ($spaceData['source'] ?? null) === 'ar-slam-localization'
                && trim((string)($spaceData['zipMd5'] ?? '')) === $zipMd5
            ) {
                return $space;
            }
        }

        return null;
    }

    private function decodeSpaceData($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array)$value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'create') {
            return;
        }

        if ($model instanceof Space && (int) $model->user_id !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You do not have permission to access this space.');
        }
    }
}
