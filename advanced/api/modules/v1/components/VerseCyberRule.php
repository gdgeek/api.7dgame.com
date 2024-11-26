<?php

namespace api\modules\v1\components;

use api\modules\v1\models\VerseCyber;
use Yii;
use yii\rbac\Rule;

class VerseCyberRule extends Rule
{
    public $name = 'verse_cyber_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }
        
        $verseCyber = VerseCyber::findOne($id);
        if (!$verseCyber) {
            return false;
        }
        
        $userid = Yii::$app->user->identity->id;
        if ($userid == $verseCyber->author_id) {
            return true;
        }
        return false;
        
    }
}
