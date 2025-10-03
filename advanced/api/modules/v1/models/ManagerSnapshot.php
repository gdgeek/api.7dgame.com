<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "manager".
 *
 * @property int $id
 * @property int $verse_id
 * @property string $type
 * @property string|null $data
 *
 * @property Verse $verse
 */
class ManagerSnapshot extends Manager
{
   function fields()
    {
        $fields = parent::fields();

        unset($fields['id']);
        unset($fields['verse_id']);
        unset($fields['data']);
       $fields['params'] = function () {
            return json_encode($this->data);
        };

        return $fields;
    }
}
