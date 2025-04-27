<?php

namespace api\modules\v1\components;

use api\modules\v1\models\VerseTags;
use api\modules\v1\models\Verse;
use yii\rbac\Rule;
use Yii;
class VerseTagsRule extends Rule
{
    public $name = 'verse_tags_rule';
    public function execute($user, $item, $params)
    {
      
        
        $post = Yii::$app->request->post();
        $verse_id = isset($post['verse_id']) ? $post['verse_id'] : null;
      
        if (!$verse_id) {
            $verse_id = isset($params['verse_id']) ? $params['verse_id'] : null;
            if(!$verse_id)
            {
              return false;
            }
        }

        $verse = Verse::findOne($verse_id);
    
      
        if (!$verse) {
            return false;
        }

        $userid = Yii::$app->user->identity->id;
        if ($userid == $verse->author_id) {

            return true;
        }
        return false;

    }
}
