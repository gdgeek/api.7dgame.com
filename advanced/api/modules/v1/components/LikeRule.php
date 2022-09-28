<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Like;
use Yii;
use yii\rbac\Rule;

class LikeRule extends Rule
{
    public $name = 'like_rule';
    public function execute($user, $item, $params)
    {
        return false;
        /*
        $post = Yii::$app->request->post();
        $user_id = isset($post['user_id']) ? $post['user_id'] : null;

        if (!$user_id) {
            $user_id = isset($params['user_id']) ? $params['user_id'] : null;
            if (!$user_id) {
                return false;
            }
        }

       

        $userid = Yii::$app->user->identity->id;
        if ($userid == $user_id) {
            return true;
        }
        return false;*/

    }
}
