<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Cyber;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaEvent;
use api\modules\v1\models\MetaKnight;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseEvent;
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
            if ($request->isPut && isset($params['id'])) {{
                $cyber = Cyber::findOne($params['id']);
                if ($cyber) {
                    return $cyber->meta->verse;
                }
            }
                if ($request->isPost && isset($post['meta_id'])) {
                    $meta = Meta::findOne($post['meta_id']);
                    if ($meta) {
                        return $meta->verse;
                    }
                }
            }
        }
        if ($controller == 'verse-space') {

            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
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

        if ($controller == 'verse-event') {

            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }

            if ($request->isPut && isset($params['id'])) {
                $event = VerseEvent::findOne($params['id']);
                if ($event) {
                    return $event->verse;
                }
            }
            if ($request->isPost && isset($post['verse_id'])) {

                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }
        }

        if ($controller == 'event-share') {
            if ($request->isGet && isset($params['verse_id'])) {
                $verse = Verse::findOne($params['verse_id']);
                return $verse;
            }
        }
        //s/1
        if ($controller == 'meta-event') {

            if ($request->isGet && isset($params['meta_id'])) {
                $meta = Meta::findOne($params['meta_id']);
                if ($meta) {
                    return $meta->verse;
                }
            }

            if ($request->isPut && isset($params['id'])) {

                $event = MetaEvent::findOne($params['id']);

                if ($event) {
                    return $event->meta->verse;
                }
            }
            if ($request->isPost && isset($post['meta_id'])) {

                $meta = Meta::findOne($post['meta_id']);
                if ($meta) {
                    return $meta->verse;
                }
            }
        }

        if ($controller == 'meta-knight') {

            if ($request->isGet && isset($params['id'])) {
                $knight = MetaKnight::findOne($params['id']);
                if ($knight) {
                    return $knight->verse;
                }

            }

            if ($request->isPost && isset($post['verse_id'])) {
                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }

            if ($request->isDelete && isset($params['id'])) {

                $knight = MetaKnight::findOne($params['id']);
                if ($knight) {
                    return $knight->verse;
                }

            }
            if ($request->isPut && isset($params['id'])) {

                $knight = MetaKnight::findOne($params['id']);
                if ($knight) {
                    return $knight->verse;
                }
            }
        }
        if ($controller == 'meta') {

            if ($request->isPost && isset($post['verse_id'])) {
                $verse = Verse::findOne($post['verse_id']);
                return $verse;
            }

            if (($request->isGet || $request->isPut || $request->isDelete) && isset($params['id'])) {

                $meta = Meta::findOne($params['id']);
                if ($meta) {
                    return $meta->verse;
                }

            }

        }
        if ($controller == 'verse') {

            if (($request->isGet || $request->isPut || $request->isDelete) && isset($params['id'])) {

                $verse = Verse::findOne($params['id']);

                return $verse;
            }

        }

        throw new BadRequestHttpException($controller . '$request->isGet');

    }

    public function execute($user, $item, $params)
    {
        $verse = $this->getVerse($params);

        if (!$verse) {
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
