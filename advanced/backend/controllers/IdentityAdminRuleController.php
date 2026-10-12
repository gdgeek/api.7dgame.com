<?php

namespace backend\controllers;

use api\modules\v1\services\IndependentIdentity;
use Yii;

class IdentityAdminRuleController extends \mdm\admin\controllers\RuleController
{
    public function getViewPath() { return Yii::getAlias('@mdm/admin/views/rule'); }
    public function actionIndex()
    {
        if (!IndependentIdentity::enabled()) return parent::actionIndex();
        $params = Yii::$app->request->queryParams;
        $params['BizRule']['name'] = is_string($params['BizRule']['name'] ?? null) ? $params['BizRule']['name'] : '';
        $model = new \mdm\admin\models\searchs\BizRule(['name' => '']);
        return $this->render('index', ['searchModel' => $model, 'dataProvider' => $model->search($params)]);
    }
}
