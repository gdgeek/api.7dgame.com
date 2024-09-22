<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseRelease;
use yii\rbac\Rule;
use Yii;

class VerseReleaseRule extends Rule
{
    public $name = 'verse_release_rule';
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
        }else if($request->isDelete){
            $id = isset($params['id']) ? $params['id'] : null;
            $open = VerseRelease::findOne(['id' => $id]);
            if($open->verse->author_id == $userid){
                return true;
            }
        }else if($request->isPut){
            $id = isset($params['id']) ? $params['id'] : null;
            $release = VerseRelease::findOne(['id' => $id]);
            if(!$release){
                throw new \yii\web\ForbiddenHttpException('VerseRelease not found');
            }
            if($release->verse->author_id == $userid){
                return true;
            }
        }
        
        return false;
        
    }
}
