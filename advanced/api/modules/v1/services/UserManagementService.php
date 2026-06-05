<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use yii\base\Component;

class UserManagementService extends Component
{
    public function buildCurrentUserPayload($user): array
    {
        $emailVerified = !empty($user->email_verified_at);
        $emailBind = null;
        if (!empty($user->email)) {
            $emailBind = [
                'email' => $user->email,
                'verified' => $emailVerified,
                'verifiedAt' => $user->email_verified_at,
                'verifiedAtFormatted' => $user->email_verified_at ? date('Y-m-d H:i:s', $user->email_verified_at) : null,
            ];
        }

        return [
            'id' => $user->id,
            'userData' => $user->data,
            'userInfo' => $user->userInfo,
            'roles' => $user->roles,
            'organizations' => User::normalizeOrganizations($user->organizations ?? []),
            'email' => $user->email,
            'emailVerified' => $emailVerified,
            'emailVerifiedAt' => $user->email_verified_at,
            'emailBind' => $emailBind,
        ];
    }
}
