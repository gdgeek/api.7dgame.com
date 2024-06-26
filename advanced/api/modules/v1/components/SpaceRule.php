<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Space;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class SpaceRule extends Rule
{
    public $name = 'space_rule';

    private function getSpaceId($params)
    {
        return $params['id'];
    }
    public function execute($user, $item, $params)
    {

        $id = $this->getSpaceId($params);
        $space = Space::findOne($id);
        if (!$space) {
            throw new BadRequestHttpException("找不到空间");
        }

        $userid = Yii::$app->user->identity->id;

        if ($userid == $space->author_id) {
            return true;
        }

        throw new BadRequestHttpException("您不是所有者");
        return false;

    }
}
