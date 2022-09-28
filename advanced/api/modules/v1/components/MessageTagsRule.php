<?php

namespace api\modules\v1\components;

use api\modules\v1\models\MessageTags;
use api\modules\v1\models\Message;
use yii\rbac\Rule;
use Yii;
class MessageTagsRule extends Rule
{
    public $name = 'message_tags_rule';
    public function execute($user, $item, $params)
    {
      
        
        $post = Yii::$app->request->post();
        $message_id = isset($post['message_id']) ? $post['message_id'] : null;
      
        if (!$message_id) {
            $message_id = isset($params['message_id']) ? $params['message_id'] : null;
            if(!$message_id)
            {
              return false;
            }
        }

        $message = Message::findOne($message_id);
    
      
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
