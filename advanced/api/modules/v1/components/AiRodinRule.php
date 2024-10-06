<?php

namespace api\modules\v1\components;

use api\modules\v1\models\AiRodin;
use api\modules\v1\components\rule\UserDeriveRule;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class AiRodinRule extends UserDeriveRule
{
    public $name = 'ai_rodin_rule';
    public $modelType = AiRodin::class;
    public $columnName = "user_id";
    
    public function execute($user, $item, $params)
    {
        $this->modelType = AiRodin::class;
        $this->columnName = 'user_id';
        return parent::execute($user, $item, $params);
    }
}
