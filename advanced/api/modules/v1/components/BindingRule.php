<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaResource;
use api\modules\v1\models\Resource;
use api\modules\v1\models\Space;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseSpace;
use api\modules\v1\models\VerseMeta;
use Yii;
use yii\rbac\Rule;

class BindingRule extends Rule
{
    public $name = 'binding_rule';
    
    public function execute($user, $item, $params)
    {
        $post = \Yii::$app->request->post();
        $userid = Yii::$app->user->identity->id;
        $controller = Yii::$app->controller->id;
        
        $request = Yii::$app->request;
        if ($controller == 'meta-resource') {
            
            if ($request->isGet) {
                if (isset($params['meta_id'])) {
                    $meta = Meta::findOne($params['meta_id']);
                    return true;
                }
                
            }
            
            if ($request->isPost) {
                if (isset($post['meta_id']) && isset($post['resource_id'])) {
                    
                    $meta = Meta::findOne($post['meta_id']);
                    $resource = Resource::findOne($post['resource_id']);
                    //   if ($meta && $meta->verse->editable() && $resource && $resource->author_id == $userid) {
                    return true;
                    //   }
                }
            }
            if ($request->isDelete) {
                if (isset($params['id'])) {
                    $mr = MetaResource::findOne($params['id']);
                    //  if ($mr && $mr->meta->verse->editable()) {
                    return true;
                    //  }
                }
            }
        }
        if ($controller == 'verse-meta') {
            
            if ($request->isGet) {
                if (isset($params['verse_id'])) {
                    $verse = Verse::findOne($params['verse_id']);
                    if ($verse && $verse->viewable()) {
                        return true;
                    }
                }
                
            }
            
            if ($request->isPost) {
                if (isset($post['verse_id']) && isset($post['meta_id'])) {
                    
                    
                    $verse = Verse::findOne($post['verse_id']);
                    $meta = Meta::findOne($post['meta_id']);
                    
                    if ($verse && $verse->editable() && $meta && $meta->author_id == $userid) {
                        return true;
                    }
                }
            }
            if ($request->isDelete) {
                if (isset($params['id'])) {
                    $vm = VerseMeta::findOne($params['id']);
                    if ($vm && $vm->verse->editable()) {
                        return true;
                    }
                }
            }
            
        }
        if ($controller == 'verse-space') {
            
            if ($request->isGet) {
                if (isset($params['verse_id'])) {
                    $verse = Verse::findOne($params['verse_id']);
                    if ($verse && $verse->viewable()) {
                        return true;
                    }
                }
                
            }
            
            if ($request->isPost) {
                if (isset($post['verse_id']) && isset($post['space_id'])) {
                    
                    $verse = Verse::findOne($post['verse_id']);
                    $space = Space::findOne($post['space_id']);
                    if ($verse && $verse->editable() && $space && $space->author_id == $userid) {
                        return true;
                    }
                }
            }
            if ($request->isDelete) {
                if (isset($params['id'])) {
                    $vs = VerseSpace::findOne($params['id']);
                    if ($vs && $vs->verse->editable()) {
                        return true;
                    }
                }
            }
            
        }
       
        return false;
        
    }
}
