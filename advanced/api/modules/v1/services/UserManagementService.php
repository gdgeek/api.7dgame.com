<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use yii\base\Component;

class UserManagementService extends Component
{
    private ?IamShadowCompareService $iamShadowCompareService = null;

    public function buildCurrentUserPayload($user): array
    {
        if (IndependentIdentity::enabled()) {
            $subject = IndependentIdentity::authority()->subject((int)$user->id);
            $verifiedAt = $subject['emailVerifiedAt'];
            $businessInfo = $user->userInfo ? $user->userInfo->toArray() : [];
            if (array_key_exists('avatarId', $subject)) {
                $avatar = $subject['avatarId'] === null ? null : \api\modules\v1\models\File::findOne((int)$subject['avatarId']);
                $businessInfo['avatar'] = $avatar === null ? null : $avatar->toArray();
            }
            $profile = $subject['userInfo'] ?? null;
            $businessInfo['info'] = is_array($profile) && array_key_exists('info', $profile)
                ? $profile['info'] : $profile;
            return [
                'id' => $subject['id'],
                'userData' => ['username' => $subject['username'], 'nickname' => $subject['nickname']],
                'userInfo' => $businessInfo,
                'roles' => $subject['roles'],
                'perms' => $subject['permissions'],
                'organizations' => User::normalizeOrganizations($subject['organizations']),
                'email' => $subject['email'],
                'emailVerified' => $verifiedAt !== null,
                'emailVerifiedAt' => $verifiedAt,
                'emailBind' => $subject['email'] === null ? null : [
                    'email' => $subject['email'], 'verified' => $verifiedAt !== null,
                    'verifiedAt' => $verifiedAt,
                    'verifiedAtFormatted' => $verifiedAt ? date('Y-m-d H:i:s', $verifiedAt) : null,
                ],
            ];
        }
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

        $payload = [
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

        $this->iamShadowCompareService()->compareCurrentUserPayload($user, $payload);

        return $payload;
    }

    private function iamShadowCompareService(): IamShadowCompareService
    {
        if ($this->iamShadowCompareService === null) {
            $this->iamShadowCompareService = new IamShadowCompareService();
        }

        return $this->iamShadowCompareService;
    }
}
