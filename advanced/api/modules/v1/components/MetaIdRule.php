<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Meta;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class MetaIdRule extends Rule
{
    public $name = 'meta_id_rule';

    private function getMeta($params)
    {

        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        $meta_id = isset($post['meta_id']) ? $post['meta_id'] : $get['meta_id'] ?? null;
       

      
        if (!$meta_id) {
            throw new BadRequestHttpException("no meta_id");
        }

        $meta = Meta::findOne($meta_id);
        if (!$meta) {
            throw new BadRequestHttpException("no meta");
        }
        return $meta;



    }

    public function execute($user, $item, $params)
    {

        $meta = $this->getMeta($params);

        if ($meta->editable()) {
            return true;
        }

        if (Yii::$app->request->isGet && $meta->viewable()) {
            return true;
        }

        throw new BadRequestHttpException(" verse cant be edited");

        // return false;

    }
}
