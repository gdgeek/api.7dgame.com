<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Organization;
use api\modules\v1\models\UserOrganization;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\Response;

class OrganizationController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                [
                    'class' => JwtHttpBearerAuth::class,
                    'throwException' => false,
                ],
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'allowActions' => ['options'],
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'list' => ['GET'],
            'create' => ['POST'],
            'update' => ['POST'],
            'bind-user' => ['POST'],
            'unbind-user' => ['POST'],
        ];
    }

    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($error = $this->requirePermission('organization.list')) {
            return $error;
        }

        $query = Organization::find();
        $names = $this->resolveOrganizationNames(Yii::$app->request->get('names'));

        if (!empty($names)) {
            $query->andWhere(['name' => $names]);
        }

        $organizations = $query->ordered()->all();

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => array_map([$this, 'serializeOrganization'], $organizations),
        ];
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($error = $this->requirePermission('organization.create')) {
            return $error;
        }

        $organization = new Organization();
        $organization->title = Yii::$app->request->getBodyParam('title');
        $organization->name = Yii::$app->request->getBodyParam('name');

        if (!$organization->save()) {
            return $this->validationError($organization);
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => $this->serializeOrganization($organization),
        ];
    }

    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($error = $this->requirePermission('organization.update')) {
            return $error;
        }

        $id = Yii::$app->request->getBodyParam('id');
        if ($id === null || $id === '') {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 4000,
                'message' => '缺少必要参数: id',
            ];
        }

        $organization = Organization::findOne((int)$id);
        if ($organization === null) {
            Yii::$app->response->statusCode = 404;
            return [
                'code' => 4004,
                'message' => '组织不存在',
            ];
        }

        $organization->title = Yii::$app->request->getBodyParam('title');

        if (!$organization->save()) {
            return $this->validationError($organization);
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => $this->serializeOrganization($organization),
        ];
    }

    public function actionBindUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($error = $this->requirePermission('organization.bind-user')) {
            return $error;
        }

        [$userId, $organizationId, $error] = $this->resolveBindingIds();
        if ($error !== null) {
            return $error;
        }

        $binding = UserOrganization::findOne([
            'user_id' => $userId,
            'organization_id' => $organizationId,
        ]);

        if ($binding === null) {
            $binding = new UserOrganization([
                'user_id' => $userId,
                'organization_id' => $organizationId,
            ]);

            if (!$binding->save()) {
                return $this->validationError($binding);
            }
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'user_id' => $userId,
                'organization_id' => $organizationId,
            ],
        ];
    }

    public function actionUnbindUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($error = $this->requirePermission('organization.bind-user')) {
            return $error;
        }

        [$userId, $organizationId, $error] = $this->resolveBindingIds();
        if ($error !== null) {
            return $error;
        }

        $binding = UserOrganization::findOne([
            'user_id' => $userId,
            'organization_id' => $organizationId,
        ]);

        if ($binding !== null) {
            $binding->delete();
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'user_id' => $userId,
                'organization_id' => $organizationId,
            ],
        ];
    }

    private function requirePermission(string $permission): ?array
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            Yii::$app->response->statusCode = 401;
            return [
                'code' => 4001,
                'message' => '未登录',
            ];
        }

        if (!Yii::$app->authManager->checkAccess($user->id, $permission)) {
            Yii::$app->response->statusCode = 403;
            return [
                'code' => 2003,
                'message' => '没有权限执行此操作',
            ];
        }

        return null;
    }

    private function resolveBindingIds(): array
    {
        $userId = Yii::$app->request->getBodyParam('user_id');
        $organizationId = Yii::$app->request->getBodyParam('organization_id');

        if ($userId === null || $userId === '' || $organizationId === null || $organizationId === '') {
            Yii::$app->response->statusCode = 400;
            return [null, null, [
                'code' => 4000,
                'message' => '缺少必要参数: user_id, organization_id',
            ]];
        }

        return [(int)$userId, (int)$organizationId, null];
    }

    private function serializeOrganization(Organization $organization): array
    {
        return [
            'id' => (int)$organization->id,
            'title' => $organization->title,
            'name' => $organization->name,
        ];
    }

    private function resolveOrganizationNames($rawNames): array
    {
        if ($rawNames === null || $rawNames === '') {
            return [];
        }

        $values = is_array($rawNames) ? $rawNames : explode(',', (string)$rawNames);
        $names = [];

        foreach ($values as $value) {
            $name = strtolower(trim((string)$value));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    private function validationError($model): array
    {
        Yii::$app->response->statusCode = 422;

        return [
            'code' => 4220,
            'message' => '参数校验失败',
            'errors' => $model->getErrors(),
        ];
    }
}
