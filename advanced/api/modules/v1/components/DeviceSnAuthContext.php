<?php

namespace api\modules\v1\components;

use api\modules\v1\exceptions\DeviceSnAuthenticationException;

/** Server-owned credential provenance, shared by JWTs and refresh records. */
final class DeviceSnAuthContext
{
    public const METHOD = 'device_sn';
    public const MAX_ACCESS_SECONDS = 10800;

    public static function normalize(array $context): array
    {
        $method = $context['auth_method'] ?? null;
        $snId = $context['device_sn_id'] ?? null;
        if ($method !== self::METHOD && $snId === null) {
            return [];
        }

        if ($method !== self::METHOD || (!is_int($snId) && !is_string($snId))
            || filter_var($snId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new DeviceSnAuthenticationException('Device SN authentication context is incomplete.');
        }

        return ['auth_method' => self::METHOD, 'device_sn_id' => (int)$snId];
    }

    public static function fromClaims($claims): array
    {
        if (($claims->has('auth_method') || $claims->has('device_sn_id'))
            && ($claims->get('auth_method', null) !== self::METHOD || $claims->get('device_sn_id', null) === null)) {
            throw new DeviceSnAuthenticationException('Device SN token source is incomplete.');
        }
        return self::normalize([
            'auth_method' => $claims->has('auth_method') ? $claims->get('auth_method') : null,
            'device_sn_id' => $claims->has('device_sn_id') ? $claims->get('device_sn_id') : null,
        ]);
    }
}
