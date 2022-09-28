<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Reply;
use yii\rbac\Rule;
use Yii;
class ReplyRule extends Rule
{
    public $name = 'reply_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }

        $reply = Reply::findOne($id);
        if (!$reply) {
            return false;
        }

        $userid = Yii::$app->user->identity->id;
        if ($userid == $reply->author_id) {
            return true;
        }
        return false;

    }
}
