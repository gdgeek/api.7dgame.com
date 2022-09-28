<?php
namespace common\components;
use api\modules\v1\models\Resource;
use yii\rbac\Rule;
use Yii;
class ResourceRule extends Rule
{
    public $name = 'resource_rule';
    public function execute($user, $item, $params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }

        $resource = Resource::findOne($id);
        if (!$resource) {
            return false;
        }

        $userid = Yii::$app->user->identity->id;
        if ($userid == $resource->author_id) {
            return true;
        }
        return false;

    }
}
