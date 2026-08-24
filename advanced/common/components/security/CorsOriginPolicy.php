<?php

namespace common\components\security;

use yii\base\Event;
use yii\web\Request;
use yii\web\Response;

/**
 * Parses exact ASCII/punycode browser origins (without a trailing slash) from
 * configuration without allowing wildcard, path, credential or non-local
 * clear-text fallbacks.
 */
final class CorsOriginPolicy
{
    private const ALLOWED_METHODS = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'HEAD',
        'OPTIONS',
    ];

    private const ALLOWED_REQUEST_HEADERS = [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-Token',
    ];

    private const EXPOSED_HEADERS = [
        'X-Pagination-Total-Count',
        'X-Pagination-Page-Count',
        'X-Pagination-Current-Page',
        'X-Pagination-Per-Page',
        'X-Identity-IAM-Role-Write',
        'X-Identity-IAM-Role-Write-Decision',
        'X-Identity-IAM-Role-Write-Correlation',
        'X-Identity-IAM-Role-Write-Route',
        'X-Identity-IAM-Role-Write-Entry',
        'X-Identity-IAM-Role-Write-Actor',
        'X-Identity-IAM-Role-Write-Selector-Kind',
        'X-Identity-IAM-AuthZ-Probe-Evidence',
    ];

    /**
     * @return list<string>
     */
    public static function fromEnvironment(string|false|null $value = null): array
    {
        if ($value === null) {
            $value = getenv('CORS_ALLOWED_ORIGINS');
        }

        if ($value === false || trim($value) === '') {
            return [];
        }

        $origins = [];
        foreach (explode(',', $value) as $candidate) {
            $origin = self::normalize(trim($candidate));
            if ($origin !== null) {
                $origins[$origin] = true;
            }
        }

        return array_keys($origins);
    }

    public static function normalize(string $candidate): ?string
    {
        if ($candidate === '' || $candidate === '*') {
            return null;
        }

        if (!preg_match(
            '/\A(?<scheme>https?):\/\/(?<host>\[[0-9a-f:.]+\]|[a-z0-9.-]+)(?::(?<port>[0-9]{1,5}))?\z/iD',
            $candidate,
            $parts
        )) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $rawHost = strtolower($parts['host']);
        $host = trim($rawHost, '[]');
        if (
            (str_starts_with($rawHost, '[') && !str_contains($host, ':'))
            || !self::isValidHost($host)
        ) {
            return null;
        }

        if ($scheme === 'http' && !self::isLoopbackHost($host)) {
            return null;
        }

        $port = isset($parts['port']) && $parts['port'] !== ''
            ? (int) $parts['port']
            : null;
        if ($port !== null && ($port < 1 || $port > 65535)) {
            return null;
        }
        if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
            $port = null;
        }

        $serializedHost = str_contains($host, ':') ? '[' . $host . ']' : $host;
        return $scheme . '://' . $serializedHost . ($port === null ? '' : ':' . $port);
    }

    /**
     * Shared Yii CORS settings. Controller-level legacy filters may be more
     * permissive, so enforceResponseEvent() is the final response boundary.
     *
     * @param list<string>|null $origins
     * @return array<string, mixed>
     */
    public static function yiiConfiguration(?array $origins = null): array
    {
        return [
            'Origin' => $origins ?? self::fromEnvironment(),
            'Access-Control-Request-Method' => self::ALLOWED_METHODS,
            'Access-Control-Request-Headers' => self::ALLOWED_REQUEST_HEADERS,
            'Access-Control-Allow-Credentials' => null,
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Expose-Headers' => self::EXPOSED_HEADERS,
        ];
    }

    /**
     * Runs at Response::EVENT_BEFORE_SEND, after every controller filter, so a
     * legacy controller cannot restore wildcard CORS headers.
     */
    public static function enforceResponseEvent(Event $event): void
    {
        if (!$event->sender instanceof Response || !\Yii::$app->has('request')) {
            return;
        }

        $request = \Yii::$app->get('request');
        if ($request instanceof Request) {
            self::enforceResponse($request, $event->sender);
        }
    }

    /**
     * @param list<string>|null $allowedOrigins
     */
    public static function enforceResponse(
        Request $request,
        Response $response,
        ?array $allowedOrigins = null
    ): void {
        $headers = $response->getHeaders();
        self::addVaryOrigin($headers);

        $origin = self::normalize((string) $request->getHeaders()->get('Origin', ''));
        $allowedOrigins ??= self::fromEnvironment();
        if ($origin === null || !in_array($origin, $allowedOrigins, true)) {
            foreach (self::corsResponseHeaderNames() as $name) {
                $headers->remove($name);
            }
            return;
        }

        $headers->set('Access-Control-Allow-Origin', $origin);
        $headers->remove('Access-Control-Allow-Credentials');
        $headers->set(
            'Access-Control-Expose-Headers',
            implode(', ', self::EXPOSED_HEADERS)
        );

        if (!$request->getIsOptions()) {
            $headers->remove('Access-Control-Allow-Methods');
            $headers->remove('Access-Control-Allow-Headers');
            $headers->remove('Access-Control-Max-Age');
            return;
        }

        $requestedMethod = strtoupper((string) $request->getHeaders()->get(
            'Access-Control-Request-Method',
            ''
        ));
        if (
            $requestedMethod === ''
            || !in_array($requestedMethod, self::ALLOWED_METHODS, true)
        ) {
            foreach (self::corsResponseHeaderNames() as $name) {
                $headers->remove($name);
            }
            return;
        }

        $headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', self::ALLOWED_METHODS)
        );
        $requestedHeaders = preg_split(
            '/[\s,]+/',
            (string) $request->getHeaders()->get('Access-Control-Request-Headers', ''),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];
        $acceptedHeaders = array_uintersect(
            $requestedHeaders,
            self::ALLOWED_REQUEST_HEADERS,
            'strcasecmp'
        );
        if ($acceptedHeaders !== []) {
            $headers->set(
                'Access-Control-Allow-Headers',
                implode(', ', $acceptedHeaders)
            );
        } else {
            $headers->remove('Access-Control-Allow-Headers');
        }
        $headers->set('Access-Control-Max-Age', '86400');
    }

    private static function isLoopbackHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private static function isValidHost(string $host): bool
    {
        if ($host === '' || str_ends_with($host, '.')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false || $host === 'localhost') {
            return true;
        }

        if (preg_match('/^[0-9.]+$/', $host) === 1) {
            return false;
        }

        return preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
            $host
        ) === 1;
    }

    /** @return list<string> */
    private static function corsResponseHeaderNames(): array
    {
        return [
            'Access-Control-Allow-Origin',
            'Access-Control-Allow-Credentials',
            'Access-Control-Allow-Methods',
            'Access-Control-Allow-Headers',
            'Access-Control-Max-Age',
            'Access-Control-Expose-Headers',
        ];
    }

    private static function addVaryOrigin(\yii\web\HeaderCollection $headers): void
    {
        $varyLines = $headers->get('Vary', [], false);
        $values = [];
        foreach ($varyLines as $line) {
            foreach (preg_split('/\s*,\s*/', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $value) {
                $value = trim($value);
                if ($value === '*') {
                    return;
                }
                $normalized = strtolower($value);
                if (!isset($values[$normalized])) {
                    $values[$normalized] = $value;
                }
            }
        }
        $values['origin'] ??= 'Origin';
        $headers->set('Vary', implode(', ', array_values($values)));
    }
}
