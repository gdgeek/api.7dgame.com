<?php

namespace api\modules\vp\helper;

use Yii;
use yii\base\Behavior;
use yii\base\Event;

class CacheBehavior extends Behavior
{
    public function events()
    {
        return [
            \yii\base\Application::EVENT_BEFORE_REQUEST => 'handleBeforeRequest',
        ];
    }

    public function handleBeforeRequest(Event $event)
    {
      

     
    }

}
