<?php

namespace api\modules\v1\components;

use api\modules\v1\models\VerseShare;
use yii\rbac\Rule;

class VerseShareRule extends Rule
{
    public $name = 'verse_share_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;

        if (!$id) {
            return false;
        }

        $share = VerseShare::findOne($id);

        $userid = \Yii::$app->user->identity->id;

        if (!$share || $share->verse->author_id != $userid) {
            return false;
        }

        return true;

    }
}
