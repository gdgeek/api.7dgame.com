<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseOpen;
use yii\rbac\Rule;
use Yii;

class VerseOpenRule extends Rule
{
    public $name = 'verse_open_rule';
    public function execute($user, $item, $params)
    {
        
        $request = Yii::$app->request;
        $userid = Yii::$app->user->identity->id;
        if($request->isPost){
            
            $post = \Yii::$app->request->post();
            $verse = Verse::findOne($post['verse_id']);
            if($verse->author_id == $userid){
                return true;
            }
        }
        if($request->isDelete){
            $id = isset($params['id']) ? $params['id'] : null;
        
        }
        
        return false;
        
    }
}
