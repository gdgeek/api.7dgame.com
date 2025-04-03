<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Verse;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseIdRule extends Rule
{
    public $name = 'verse_id_rule';

    private function getVerse($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        $verse_id = isset($post['verse_id']) ? $post['verse_id'] : $get['verse_id'] ?? null;
       
         
        if (!$verse_id) {
            throw new BadRequestHttpException("no verse_id");
        }

        $verse = Verse::findOne($verse_id);
        if (!$verse) {
            throw new BadRequestHttpException("no verse");
        }
        return $verse;



    }

    public function execute($user, $item, $params)
    {

        $verse = $this->getVerse($params);

        if ($verse->editable()) {
            return true;
        }

        //  $request = Yii::$app->request;
        if (Yii::$app->request->isGet && $verse->viewable()) {
            return true;
        }

        throw new BadRequestHttpException(" verse cant be edited");

        // return false;

    }
}
