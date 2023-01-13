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
        /*
    $post = Yii::$app->request->post();

    $id = isset($params['id']) ? $params['id'] : null;
    if (!$id) {
    $id = isset($params['space_id']) ? $params['space_id'] : null;
    if (!$id) {
    $post = Yii::$app->request->post();
    $id = isset($post['space_id']) ? $post['space_id'] : null;
    if (!$id) {
    throw new BadRequestHttpException(json_encode($item));
    }
    }
    }
     */

    }
}
