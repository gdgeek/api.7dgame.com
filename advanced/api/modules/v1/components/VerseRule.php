<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Cyber;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaKnight;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseScript;
use api\modules\v1\models\VerseShare;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseRule extends Rule
{
    public $name = 'verse_rule';
    
    private function getVerse($params)
    {
        
        $request = Yii::$app->request;
        
        $post = \Yii::$app->request->post();
        $controller = Yii::$app->controller->id;
        
        if ($controller == 'cyber') {
            if (($request->isPut || $request->isGet) && isset($params['id'])) {
                $cyber = Cyber::findOne($params['id']);
                if ($cyber) {
                    return null;
                }
            }
            
            if ($request->isPost && isset($post['meta_id'])) {
                $meta = Meta::findOne($post['meta_id']);
                if ($meta) {
                    return null;
                }
            }
        }
        if ($controller == 'verse-space') {
            
            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }
        }
        if($controller == 'verse-script'){
            
            
            
            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }
            
            if ($request->isPost && isset($post['verse_id'])) {
                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }
            
            if (($request->isDelete || $request->isPut || $request->isGet) && isset($params['id'])) {
                
                $script = VerseScript::findOne($params['id']);
                $verse = Verse::findOne($script->verse_id);
                return $verse;
            }
        }
        if ($controller == 'verse-share') {
            
            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }
            
            if ($request->isPost && isset($post['verse_id'])) {
                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }
            if (($request->isDelete || $request->isPut) && isset($params['id'])) {
                
                $share = VerseShare::findOne($params['id']);
                return $share->verse;
            }
            
        }
        
        if ($controller == 'event-share') {
            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }
        }
        
        if ($controller == 'meta-knight') {
            
            //throw new BadRequestHttpException($controller . '$request->isGet');
            
            if (($request->isGet || $request->isDelete || $request->isPut) && isset($params['id'])) {
                $knight = MetaKnight::findOne($params['id']);
                if ($knight) {
                    return $knight->verse;
                }
                
            }
            
            if ($request->isPost && isset($post['verse_id'])) {
                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }
            
        }
        if ($controller == 'meta') {
            
            return null;
            
        }
        
        if ($controller == 'verse') {
            
            if (($request->isGet || $request->isPut || $request->isDelete) && isset($params['id'])) {
                
                $verse = Verse::findOne($params['id']);
                
                return $verse;
            }
            
        }
        
        throw new BadRequestHttpException($controller . '$request->isGet!!!!');
        
    }
    
    public function execute($user, $item, $params)
    {
        
        $verse = $this->getVerse($params);
        
        if (!$verse) {
            return true;
            throw new BadRequestHttpException("no verse");
        }
        
        if ($verse->editable()) {
            return true;
        }
        
        $request = Yii::$app->request;
        if ($request->isGet && $verse->viewable()) {
            return true;
        }
        
        return false;
        
    }
}
