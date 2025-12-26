<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Verse;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseReleaseRule extends Rule
{
    public $name = 'verse_rule';
    
    private function getVerse($params)
    {
        
        $request = Yii::$app->request;
        
       // $post = \Yii::$app->request->post();
        $controller = Yii::$app->controller->id;
     
        if ($controller == 'verse' && isset($params['id'])) {
            $verse = Verse::findOne($params['id']);
            return $verse;
        }
        
        throw new BadRequestHttpException($controller . json_encode($params));
        
    }
    
    public function execute($user, $item, $params)
    {
        
        $verse = $this->getVerse($params);
        if (!$verse) {
            throw new BadRequestHttpException("no verse");
        }

        if ($verse->editable) {
            return true;
        }
        
        $request = Yii::$app->request;
        
        if ($request->isGet && $verse->viewable) {
            return true;
        }
        
        return false;
        
    }
}
