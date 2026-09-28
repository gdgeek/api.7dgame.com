<?php

namespace api\modules\v1\services;

use api\modules\v1\components\DeviceSnAuthContext;
use api\modules\v1\models\User;
use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;
use Yii;

/** Live, server-owned authorization for plugin management APIs. */
class PluginAccessService
{
    private const ROLE_LEVELS = ['user' => 1, 'manager' => 2, 'admin' => 3, 'root' => 4];
    private const SCOPE_LEVELS = ['auth-only' => 1, 'manager-only' => 2, 'admin-only' => 3, 'root-only' => 4];
    private ?PluginAccessConfigClient $client;

    public function __construct(?PluginAccessConfigClient $client = null)
    {
        $this->client = $client;
    }

    /** @return array{allowed: bool, access_scope: ?string} */
    public function resolve(string $pluginId, User $identity): array
    {
        // Device credentials must never acquire management privileges, even if
        // an operator configures auth-only or later elevates the bound account.
        if ((int)$identity->status !== 10 || DeviceSnAuthContext::normalize($identity->authContext) !== []) {
            return ['allowed' => false, 'access_scope' => null];
        }

        try {
            $client = $this->client ?? Yii::$app->get('pluginAccessConfigClient');
            // No permission cache: root-only must revoke the very next request.
            // Identity and roles stay local. The authority only exposes public
            // plugin metadata; no caller credential is sent or verified again.
            $row = $client->read($pluginId);
            if (!$row) {
                return ['allowed' => false, 'access_scope' => null];
            }
            $scope = $row['access_scope'] ?? null;
            if (!is_string($scope) || !isset(self::SCOPE_LEVELS[$scope])) {
                throw new \RuntimeException('Plugin access scope is invalid.');
            }
            if (($row['enabled'] ?? null) !== true) {
                return ['allowed' => false, 'access_scope' => $scope];
            }
            $roles = array_keys(Yii::$app->authManager->getRolesByUser($identity->id));
            $level = 0;
            foreach ($roles as $role) {
                $level = max($level, self::ROLE_LEVELS[$role] ?? 0);
            }
            return ['allowed' => $level >= self::SCOPE_LEVELS[$scope], 'access_scope' => $scope];
        } catch (\Throwable $exception) {
            // No stale permission fallback and no SQL/DSN/connection details in
            // either the public exception or its previous-exception chain.
            throw new PluginAccessConfigUnavailableException();
        }
    }
}
