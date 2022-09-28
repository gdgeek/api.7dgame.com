<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Verse;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseRule extends Rule
{
    public $name = 'verse_rule';
    public function execute($user, $item, $params)
    {
        //echo 123;

        $post = Yii::$app->request->post();

        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            $id = isset($params['verse_id']) ? $params['verse_id'] : null;
            if (!$id) {
                $post = Yii::$app->request->post();
                $id = isset($post['verse_id']) ? $post['verse_id'] : null;
                if (!$id) {
                    throw new BadRequestHttpException(json_encode($item));
                }
            }
        }

        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new BadRequestHttpException("找不到宇宙");
        }

        $userid = Yii::$app->user->identity->id;

        if ($userid == $verse->author_id || $verse->share) {
            return true;
        }

        throw new BadRequestHttpException("您不是所有者");

    }
}
