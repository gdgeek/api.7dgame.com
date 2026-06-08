<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

class AuthorizationService extends Component
{
    public function getRoleNamesByUserId(int $userId): array
    {
        return array_keys(Yii::$app->authManager->getRolesByUser($userId));
    }
}
