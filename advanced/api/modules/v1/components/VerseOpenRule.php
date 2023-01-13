<?php

namespace api\modules\v1\components;

use api\modules\v1\models\VerseOpen;
use yii\rbac\Rule;

class VerseOpenRule extends Rule
{
    public $name = 'verse_open_rule';
    public function execute($user, $item, $params)
    {

        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            $id = isset($params['verse_id']) ? $params['verse_id'] : null;
            if (!$id) {
                $post = \Yii::$app->request->post();
                $id = isset($post['verse_id']) ? $post['verse_id'] : null;
                if (!$id) {
                    throw new BadRequestHttpException(json_encode($item));
                }
            }
        }

        $open = VerseOpen::findOne(['verse_id' => $id]);
        if (!$open) {

            return false;
        }

        return true;

    }
}
