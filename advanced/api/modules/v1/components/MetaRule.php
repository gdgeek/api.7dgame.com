<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Meta;
use Yii;
use yii\rbac\Rule;

class MetaRule extends Rule
{
    public $name = 'meta_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }

        $meta = Meta::findOne($id);
        if (!$meta) {
            return false;
        }
        $userid = Yii::$app->user->identity->id;

        if ($userid == $meta->author_id) {
            return true;
        }
        return false;

    }
}
