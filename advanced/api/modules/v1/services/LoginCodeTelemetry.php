<?php

namespace api\modules\v1\services;

use Yii;

/**
 * Low-cardinality, redacted rollout events for the login-code protocol.
 *
 * Deliberately accepts only fixed event/source enums. Do not add user IDs,
 * login codes, digests, payloads, tokens, IPs, or exception messages here.
 */
final class LoginCodeTelemetry
{
    private const EVENTS = [
        'issued',
        'dual_write_success',
        'redis_write_failed',
        'db_write_failed',
        'compensation_failed',
        'rate_limited',
        'rate_limit_error',
        'redis_hit',
        'db_fallback_hit',
        'miss',
        'active',
        'expired',
        'malformed',
        'redis_error',
        'readiness_down',
    ];

    private const SOURCES = [
        'main-api-issue',
        'main-api-refresh',
        'main-api-status',
        'main-api-readiness',
        'yii3-refresh',
        'yii3-key-to-token',
    ];

    public static function record(string $event, string $source): void
    {
        if (!in_array($event, self::EVENTS, true) || !in_array($source, self::SOURCES, true)) {
            Yii::warning('Rejected invalid login-code telemetry dimensions.', 'login-code');
            return;
        }

        Yii::info([
            'event' => $event,
            'source' => $source,
        ], 'login-code');
    }
}
