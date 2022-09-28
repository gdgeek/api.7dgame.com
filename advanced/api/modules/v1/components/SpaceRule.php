<?php

namespace api\modules\v1\components;

use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class SpaceRule extends Rule
{
    public $name = 'space_rule';
    public function execute($user, $item, $params)
    {
        //echo 123;

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

        $space = Space::findOne($id);
        if (!$space) {
            throw new BadRequestHttpException("找不到空间");
        }

        $userid = Yii::$app->user->identity->id;

        if ($userid == $space->author_id) {
            return true;
        }

        throw new BadRequestHttpException("您不是所有者");

    }
}
