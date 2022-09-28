<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Message;
use yii\rbac\Rule;
use Yii;
class MessageRule extends Rule
{
    public $name = 'message_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }

        $message = Message::findOne($id);
        if (!$message) {
            return false;
        }

        $userid = Yii::$app->user->identity->id;
        if ($userid == $message->author_id) {
            return true;
        }
        return false;

    }
}
