<?php

namespace backend\controllers;

use api\modules\v1\services\IndependentIdentity;
use backend\models\IdentityAdminUserSearch;
use backend\models\IdentityAdminUserView;
use common\components\IdentityLegacyAdmin;
use Yii;

class IdentityAdminAssignmentController extends \mdm\admin\controllers\AssignmentController
{
    public function getViewPath() { return Yii::getAlias('@mdm/admin/views/assignment'); }
    public function actionIndex()
    {
        if (!IndependentIdentity::enabled()) return parent::actionIndex();
        $search = new IdentityAdminUserSearch(['searchForm' => 'Assignment']);
        return $this->render('index', ['searchModel' => $search, 'dataProvider' => $search->search(Yii::$app->request->queryParams),
            'idField' => $this->idField, 'usernameField' => $this->usernameField, 'extraColumns' => $this->extraColumns]);
    }
    protected function findModel($id)
    {
        if (!IndependentIdentity::enabled()) return parent::findModel($id);
        $user = new IdentityAdminUserView(IdentityLegacyAdmin::call('user-view', ['id' => (int)$id]));
        return new \mdm\admin\models\Assignment($id, $user);
    }
}
