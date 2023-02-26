<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Cyber;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaEvent;
use api\modules\v1\models\MetaKnight;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseEvent;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseRule extends Rule
{
    public $name = 'verse_rule';
    private function getVerseFromVerseId($params)
    {

        $id = isset($params['verse_id']) ? $params['verse_id'] : $params['verseId'];

        return $id;
    }
    private function getVerseIdMetaEvent($params)
    {
        $post = \Yii::$app->request->post();

        if (isset($post['verse_id'])) {
            return $post['verse_id'];
        }

        $id = isset($params['metaId']) ? $params['metaId'] : $params['id'];
        $meta = Meta::findOne($id);
        if ($meta) {
            return $meta->verse->id;
        }
        throw new BadRequestHttpException($id);

    }
    private function getVerseId($params)
    {
        $request = Yii::$app->request;

        $post = \Yii::$app->request->post();
        $controller = Yii::$app->controller->id;
        if ($controller == 'cyber') {
            if ($request->isPut) {
                return $this->getIdFromCyberId($params, 'id');
            }
            if ($request->isPost) {

                return $this->getIdFromMetaId($post, 'meta_id');
            }
        }
        if ($controller == 'verse-space') {

            if ($request->isGet) {

                return $this->getId($params, 'verse_id');
            }
        }
        if ($controller == 'verse-share') {

            if ($request->isGet) {
                return $this->getId($params, 'verse_id');
            }
            if ($request->isPost) {
                return $this->getId($post, 'verse_id');
            }
        }

        if ($controller == 'verse-event') {
            if ($request->isGet) {

                return $this->getId($params, 'verseId');
            }
            if ($request->isPut) {

                return $this->getIdFromVerseEventId($params, 'id');
            }
            if ($request->isPost) {

                return $this->getId($post, 'verse_id');
            }
        }

        if ($controller == 'event-share') {
            if ($request->isGet) {

                return $this->getId($params, 'verseId');
            }
        }
        //s/1
        if ($controller == 'meta-event') {
            if ($request->isGet) {

                return $this->getIdFromMetaId($params, 'metaId');
            }

            if ($request->isPut) {

                return $this->getIdFromMetaEventId($params, 'id');
            }
            if ($request->isPost) {

                return $this->getIdFromMetaId($post, 'meta_id');
            }
        }

        if ($controller == 'meta-knight') {
            if ($request->isPost) {
                return $this->getId($post, 'verse_id');
            }
            if ($request->isGet) {
                return $this->getIdFromMetaKnight($params, 'id');
            }
            if ($request->isDelete) {
                return $this->getIdFromMetaKnight($params, 'id');
            }
            if ($request->isPut) {

                return $this->getIdFromMetaKnight($params, 'id');
            }
        }
        if ($controller == 'meta') {
            if ($request->isGet) {
                return $this->getIdFromMetaId($params, 'id');
            }
            if ($request->isPost) {

                return $this->getId($post, 'verse_id');
            }

            if ($request->isPut) {

                return $this->getIdFromMetaId($params, 'id');
            }
            if ($request->isDelete) {

                return $this->getIdFromMetaId($params, 'id');
            }
        }
        if ($controller == 'verse') {
            return $this->getId($params, 'id');
        }

        throw new BadRequestHttpException($controller);

    }
    private function getIdFromCyberId($array, $key)
    {
        if (isset($array[$key])) {

            $id = $array[$key];

            $cyber = Cyber::findOne($id);

            if ($cyber) {
                return $cyber->meta->verse->id;
            }
        }

        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    private function getIdFromMetaKnight($array, $key)
    {
        if (isset($array[$key])) {

            $id = $array[$key];

            $knight = MetaKnight::findOne($id);

            if ($knight) {
                return $knight->verse->id;
            }
        }

        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    private function getIdFromMetaEventId($array, $key)
    {
        if (isset($array[$key])) {

            $id = $array[$key];

            $event = MetaEvent::findOne($id);

            if ($event) {
                return $event->meta->verse->id;
            }
        }

        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    private function getIdFromVerseEventId($array, $key)
    {

        if (isset($array[$key])) {

            $id = $array[$key];

            $event = VerseEvent::findOne($id);

            if ($event) {
                return $event->verse->id;
            }
        }

        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    private function getIdFromMetaId($array, $key)
    {
        if (isset($array[$key])) {

            $id = $array[$key];

            $meta = Meta::findOne($id);

            if ($meta) {
                return $meta->verse->id;
            }
        }

        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    private function getId($array, $key)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        throw new BadRequestHttpException(json_encode($array));
        return null;
    }
    public function execute($user, $item, $params)
    {

        $id = $this->getVerseId($params);

        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new BadRequestHttpException("no verse");
        }

        $userid = Yii::$app->user->identity->id;

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
