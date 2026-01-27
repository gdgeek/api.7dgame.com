<?php

namespace api\modules\v1\models;

//use api\modules\v1\models\Cyber;
use api\modules\v1\models\File;
use api\modules\v1\models\User;
use Yii;
use api\modules\v1\models\MetaCode;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use api\modules\v1\components\Validator\JsonValidator;

/**
 * This is the model class for table "meta".
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property string|null $info
 * @property int|null $image_id
 * @property string|null $data
 * @property string|null $uuid
 *
 * @property User $author
 * @property File $image
 * @property User $updater
 * @property string|null $events
 * @property string|null $title
 *
 */
class MetaSnapshot extends Meta
{

    public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['image_id']);
        unset($fields['info']);
        $fields['type'] = function ($model) {
            return $model->prefab == 0 ? 'entity' : 'prefab';
        };
        $fields['data'] = function () {
            return $this->data;
        };

        $fields['code'] = function () {
            return $this->getCode();
        };

        unset($fields['prefab']);
        unset($fields['resources']);
        unset($fields['editable']);
        unset($fields['viewable']);

        return $fields;
    }

    public function getCode()
    {

        $cl = Yii::$app->request->get('cl');
        $substring = "";
        if (!$cl) {
            $cl = 'lua';
            $substring = "local meta = {}\nlocal index = ''\n";
        }

        $code = $this->metaCode;
        if ($code === null) {
            return $substring;
        }
        $script = null;
        if ($cl === 'lua') {
            $script = $code->lua;
        } else if ($cl === 'js') {
            $script = $code->js;
        }

        if (isset($script)) {
            if (strpos($script, $substring) !== false) {
                return $script;
            } else {
                return $substring . $script;
            }
        } else {
            return $substring;
        }
    }
    /*
    public function getCode()
    {

        $metaCode = $this->metaCode;
        $cl = Yii::$app->request->get('cl');
        if (!$cl) {
            $cl = 'lua';
        }
        if ($metaCode && $metaCode->code) {
            $script = $metaCode->code->$cl;
        }

        if ($cl == 'lua') {
            $substring = "local meta = {}\nlocal index = ''\n";
        } else {
            $substring = '';
        }


        if (isset($script)) {
            if (strpos($script, $substring) !== false) {
                return $script;
            } else {
                return $substring . $script;
            }
        } else {
            return $substring;
        }
    }*/
}
