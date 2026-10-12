<?php

namespace backend\controllers;

use api\modules\v1\services\IndependentIdentity;
use Yii;

/** MDM 2.12 passes null to trim on PHP8.4; keep its view and safe search values. */
class IdentityAdminRoleController extends \mdm\admin\controllers\RoleController
{
    use IdentityAdminItemSearch;
}

trait IdentityAdminItemSearch
{
    public function actionGetUsers($id)
    {
        if (!IndependentIdentity::enabled()) return parent::actionGetUsers($id);
        $this->findModel($id);
        $provider = new \yii\data\ArrayDataProvider([
            'allModels' => Yii::$app->authManager->getUserIdsByRole($id),
            'pagination' => ['pageSize' => min(100, max(1, (int)\mdm\admin\components\Configs::userRolePageSize()))],
        ]);
        $result = ['users' => []];
        foreach ($provider->models as $userId) {
            $user = \common\components\IdentityLegacyAdmin::call('user-view', ['id' => (int)$userId]);
            $result['users'][] = ['id' => (int)$user['id'], 'username' => $user['username'],
                'link' => \yii\helpers\Url::to(['/admin/assignment/view', 'id' => $user['id']])];
        }
        $page = $provider->pagination->getPage();
        $count = $provider->pagination->getPageCount();
        if ($count > 0) {
            $result['first'] = 0; $result['last'] = $count - 1;
            if ($page > 0) $result['prev'] = $page - 1;
            if ($page < $count - 1) $result['next'] = $page + 1;
        }
        Yii::$app->response->format = 'json';
        return $result;
    }

    public function actionIndex()
    {
        if (!IndependentIdentity::enabled()) return parent::actionIndex();
        $params = Yii::$app->request->queryParams;
        foreach (['name', 'description', 'ruleName'] as $name) {
            $params['AuthItem'][$name] = is_string($params['AuthItem'][$name] ?? null) ? $params['AuthItem'][$name] : '';
        }
        $model = new \mdm\admin\models\searchs\AuthItem(['type' => $this->type, 'name' => '', 'description' => '', 'ruleName' => '']);
        return $this->render('index', ['searchModel' => $model, 'dataProvider' => $model->search($params)]);
    }
}
