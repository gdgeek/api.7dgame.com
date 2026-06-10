<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

class AuthorizationService extends Component
{
    private ?IamShadowCompareService $iamShadowCompareService = null;

    public function getRoleNamesByUserId(int $userId): array
    {
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($userId));
        $this->iamShadowCompareService()->compareRolesByUserId($userId, $roles, 'authorization');

        return $roles;
    }

    private function iamShadowCompareService(): IamShadowCompareService
    {
        if ($this->iamShadowCompareService === null) {
            $this->iamShadowCompareService = new IamShadowCompareService();
        }

        return $this->iamShadowCompareService;
    }
}
