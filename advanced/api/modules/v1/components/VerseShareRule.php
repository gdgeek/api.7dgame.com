<?php

namespace api\modules\v1\components;

use api\modules\v1\models\VerseShare;
use yii\rbac\Rule;

class VerseShareRule extends Rule
{
    public $name = 'verse_share_rule';
    public function execute($user, $item, $params)
    {

        $user_id = isset($params['user_id']) ? $params['user_id'] : null;
        if (!$user_id) {
            return false;
        }

        $verse_id = isset($params['verse_id']) ? $params['verse_id'] : null;
        if (!$verse_id) {
            return false;
        }

        //$query = Verse::find();
        $share = VerseShare::findOne(['verse_id' => $verse_id, 'user_id' => $user_id]);
        if (!$share) {
            return false;
        }

        return true;

    }
}
