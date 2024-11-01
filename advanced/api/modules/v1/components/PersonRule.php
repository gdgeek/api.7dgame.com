<?php

namespace api\modules\v1\components;

use Yii;
use yii\rbac\Rule;

class PersonRule extends Rule
{
    public $name = 'person_rule';

    private function getPersen($params)
    {
        $request = Yii::$app->request;
        if ($request->isGet || $request->isPut || $request->isDelete) {
            $id = ArrayHelper::getValue($params, 'id', null);
            if ($id != null) {
                $persen = Persen::findOne($id);
                return $persen;
            }
            throw new Exception("无效id", 400);
        }
    }
    public function execute($user, $item, $params)
    {

        $request = Yii::$app->request;

        $roles = Yii::$app->user->identity->roles;

        if ($request->isPost) {
            if (!in_array("root", $roles) && !in_array("admin", $roles) && !in_array("manager", $roles)) {
                throw new Exception("没有此权限", 400);
                return false;
            }
            return true;
        }

        $persen = $this->getPersen($params);

        if (!$persen) {
            throw new Exception(json_encode("无法找到目标", 400));
        }
        if (Yii::$app->user->id == $persen->id) {
            throw new Exception("不能修改自己", 400);
        }

        if (in_array("root", $persen->roles)) {
            throw new Exception("不处理能root用户", 400);
        }

        if (!in_array("root", $roles)) {
            if (in_array("manager", $persen->roles) || in_array("admin", $persen->roles)) {
                throw new Exception("不能处理manager/admin用户", 400);
            }
        }

        if (!in_array("root", $roles) && !in_array("admin", $roles) && !in_array("manager", $roles)) {
            throw new Exception("没有权限", 400);
        }

        return true;

    }
}
