<?php

declare(strict_types=1);

namespace common\components\admission;

/** Private same-host protocol only. No public control endpoint or credential generation. */
final class CoordinatorClient
{
    private string $url;
    private string $key;
    private array $identity;

    public function __construct(array $environment)
    {
        $this->url = (string) ($environment['IDENTITY_DEV_ADMISSION_CONTROL_URL'] ?? '');
        if (!preg_match('#^http://127\.0\.0\.1:([1-9][0-9]{0,4})/$#D', $this->url, $match) || (int) $match[1] > 65535) self::fail();
        $encoded = (string) ($environment['IDENTITY_DEV_ADMISSION_KEY'] ?? '');
        $bytes = base64_decode(strtr($encoded, '-_', '+/'), true);
        if (!preg_match('/^[A-Za-z0-9_-]{43,86}$/D', $encoded) || $bytes === false || strlen($bytes) < 32 || strlen($bytes) > 64
            || rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=') !== $encoded) self::fail();
        $this->key = $encoded;
        $children = (string) ($environment['IDENTITY_DEV_ADMISSION_CHILDREN'] ?? '');
        $this->identity = [
            'id' => (string) ($environment['IDENTITY_DEV_ADMISSION_INSTANCE_ID'] ?? ''),
            'bootId' => (string) ($environment['IDENTITY_DEV_ADMISSION_BOOT_ID'] ?? ''),
            'revision' => (string) ($environment['IDENTITY_DEV_ADMISSION_REVISION'] ?? ''),
            'routerHash' => (string) ($environment['IDENTITY_DEV_ADMISSION_ROUTER_HASH'] ?? ''),
            'generation' => (string) ($environment['IDENTITY_DEV_ADMISSION_GENERATION'] ?? ''),
            'requiresSessionClose' => true,
            'allowedChildren' => $children === '' ? [] : explode(',', $children),
        ];
        if (($environment['IDENTITY_DEV_ADMISSION_ENVIRONMENT'] ?? '') !== 'xrugc-development'
            || !preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $this->identity['id'])
            || !preg_match('/^[A-Za-z0-9_-]{8,80}$/D', $this->identity['bootId'])
            || !preg_match('/^[a-f0-9]{40}$/D', $this->identity['revision'])
            || !preg_match('/^[a-f0-9]{64}$/D', $this->identity['routerHash'])
            || !in_array($this->identity['generation'], ['legacy', 'identity'], true)
            || count($this->identity['allowedChildren']) > 128 || count(array_unique($this->identity['allowedChildren'])) !== count($this->identity['allowedChildren'])) self::fail();
        foreach ($this->identity['allowedChildren'] as $child) if (!preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $child)) self::fail();
    }

    public static function fromEnvironment(): self
    {
        $values = [];
        foreach (['CONTROL_URL', 'KEY', 'INSTANCE_ID', 'BOOT_ID', 'REVISION', 'ROUTER_HASH', 'GENERATION', 'CHILDREN', 'ENVIRONMENT'] as $suffix) {
            $name = 'IDENTITY_DEV_ADMISSION_' . $suffix; $value = getenv($name); if ($value !== false) $values[$name] = $value;
        }
        return new self($values);
    }

    public function call(string $operation, array $payload = [], int $timeoutMs = 2000): array
    {
        if ($timeoutMs < 1 || $timeoutMs > 2000 || !function_exists('curl_init')) self::fail();
        $deadlineNs = hrtime(true) + $timeoutMs * 1000000;
        if (!in_array($operation, ['register', 'begin', 'enter', 'validate', 'continue', 'complete', 'unknown', 'lost', 'status', 'close-ack'], true)) self::fail();
        $bytes = json_encode($payload === [] ? new \stdClass() : $payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        if (strlen($bytes) > 8192) self::fail();
        $headers = [
            'Authorization: Bearer ' . $this->key, 'Content-Type: application/json', 'Connection: close',
            'X-Identity-Instance-Id: ' . $this->identity['id'], 'X-Identity-Instance-Boot-Id: ' . $this->identity['bootId'],
            'X-Identity-Instance-Revision: ' . $this->identity['revision'], 'X-Identity-Router-Hash: ' . $this->identity['routerHash'],
            'X-Identity-Generation: ' . $this->identity['generation'],
        ];
        $remaining = (int) floor(($deadlineNs - hrtime(true)) / 1000000);
        if ($remaining < 1) self::fail();
        $response = ''; $bounded = false;
        $curl = curl_init($this->url . $operation);
        if ($curl === false) self::fail();
        try {
            // A stream timeout is only an idle timeout: a peer can otherwise
            // keep a mutation ACK pending forever by dribbling headers/body.
            if (!curl_setopt_array($curl, [
                CURLOPT_POST => true, CURLOPT_POSTFIELDS => $bytes, CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0, CURLOPT_NOSIGNAL => true,
                CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*',
                CURLOPT_CONNECTTIMEOUT_MS => $remaining, CURLOPT_TIMEOUT_MS => $remaining,
                CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$response, &$bounded, $deadlineNs): int {
                    if (strlen($response) + strlen($chunk) > 65536 || hrtime(true) >= $deadlineNs) { $bounded = true; return 0; }
                    $response .= $chunk; return strlen($chunk);
                },
            ])) self::fail();
            $ok = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } finally { unset($curl); }
        // No URL, bearer context, libcurl error string or private body enters an error.
        if ($ok !== true || $bounded || hrtime(true) >= $deadlineNs) self::fail();
        try { $result = json_decode($response, true, 32, JSON_THROW_ON_ERROR); } catch (\Throwable) { self::fail(); }
        if (!is_array($result) || array_is_list($result) || hrtime(true) >= $deadlineNs) self::fail();
        if ($status !== 200) {
            $code = $result['code'] ?? null;
            // The coordinator uses 409 for journal/unknown failures too. Only
            // reviewed pre-mutation refusals may avoid poisoning another lease.
            $known = [
                'begin' => ['ROOT_ADMISSION_CLOSED', 'REVIEWED_GENERATION_NOT_READY', 'MAX_INFLIGHT_REACHED', 'PENDING_CONTROL_BOUND_REACHED'],
                'enter' => ['INVALID_OPERATION_CAPABILITY', 'OPERATION_NOT_ACTIVE', 'HANDLER_CAPABILITY_ALREADY_ENTERED_OR_WRONG_INSTANCE'],
                'continue' => ['CONTINUATION_NOT_AUTHORIZED', 'CONTINUATION_TARGET_NOT_REVIEWED', 'MAX_INFLIGHT_REACHED', 'CONTINUATION_DEPTH_REACHED'],
            ];
            if ($status === 409 && array_keys($result) === ['code'] && is_string($code) && in_array($code, $known[$operation] ?? [], true)) throw new ControlRefusal($code);
            self::fail();
        }
        if (in_array($operation, ['begin', 'continue'], true)) {
            self::exact($result, ['capability', 'operationId', 'epoch']); self::capability($result['capability']); self::uuid($result['operationId']); self::epoch($result['epoch']);
            $claims = self::capabilityClaims($result['capability']);
            if ($claims['operationId'] !== $result['operationId'] || $claims['epoch'] !== $result['epoch']
                || $claims['instanceId'] !== ($operation === 'begin' ? $this->identity['id'] : ($payload['targetId'] ?? null))) self::fail();
            if ($operation === 'begin') {
                if ($claims['rootOperationId'] !== $claims['operationId']) self::fail();
            } else {
                $parent = self::capabilityClaims($payload['capability'] ?? null);
                if ($claims['rootOperationId'] !== $parent['rootOperationId'] || $claims['epoch'] !== $parent['epoch']
                    || $claims['operationId'] === $parent['operationId']) self::fail();
            }
        } elseif ($operation === 'enter' || $operation === 'validate') {
            self::exact($result, $operation === 'enter' ? ['operationId', 'rootOperationId', 'epoch'] : ['operationId', 'rootOperationId', 'epoch', 'unknown', 'childrenActive']);
            self::uuid($result['operationId']); self::uuid($result['rootOperationId']); self::epoch($result['epoch']);
            // Decoding is not authentication. Only the successful private enter
            // authorizes a scope; this comparison rejects a mismatched ACK.
            $claims = self::capabilityClaims($payload['capability'] ?? null);
            if ($claims['instanceId'] !== $this->identity['id']) self::fail();
            foreach (['operationId', 'rootOperationId', 'epoch'] as $field) if ($claims[$field] !== $result[$field]) self::fail();
            if ($operation === 'validate' && (!is_bool($result['unknown']) || !is_int($result['childrenActive']) || $result['childrenActive'] < 0)) self::fail();
        } elseif (in_array($operation, ['unknown', 'lost'], true)) {
            self::exact($result, ['accepted']); if ($result['accepted'] !== true) self::fail();
        } else {
            if (!in_array($result['phase'] ?? null, ['legacy_open', 'closing', 'drained', 'prepared', 'committing', 'identity_open'], true)
                || !is_bool($result['unknownSealed'] ?? null) || !is_int($result['active'] ?? null) || $result['active'] < 0
                || !is_int($result['unknown'] ?? null) || $result['unknown'] < 0 || !is_array($result['instances'] ?? null)) self::fail();
            self::epoch($result['epoch'] ?? null);
            $own = array_values(array_filter($result['instances'], fn ($row) => is_array($row) && ($row['id'] ?? null) === $this->identity['id']));
            if (count($own) !== 1 || ($own[0]['bootId'] ?? null) !== $this->identity['bootId'] || ($own[0]['generation'] ?? null) !== $this->identity['generation']
                || !is_bool($own[0]['registered'] ?? null) || !is_bool($own[0]['lost'] ?? null)
                || ($operation !== 'status' && ($own[0]['registered'] !== true || $own[0]['lost'] !== false || $result['unknownSealed'] !== false))
                || !is_int($own[0]['lastBegin'] ?? null) || $own[0]['lastBegin'] < 0 || !is_int($own[0]['lastEnd'] ?? null)
                || $own[0]['lastEnd'] < 0 || $own[0]['lastEnd'] > $own[0]['lastBegin']) self::fail();
        }
        if (hrtime(true) >= $deadlineNs) self::fail();
        return $result;
    }

    public function register(int $timeoutMs = 2000): void { $this->call('register', ['identity' => $this->identity], $timeoutMs); }
    public function begin(int $timeoutMs = 2000): string { return $this->call('begin', [], $timeoutMs)['capability']; }
    private static function exact(array $value, array $fields): void
    {
        $keys = array_keys($value); sort($keys); sort($fields); if ($keys !== $fields) self::fail();
    }
    private static function uuid(mixed $value): void
    {
        if (!is_string($value) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D', $value)) self::fail();
    }
    private static function epoch(mixed $value): void
    {
        if (!is_int($value) || $value < 1 || $value > 9007199254740991) self::fail();
    }
    private static function capabilityClaims(mixed $value): array
    {
        self::capability($value); $encoded = explode('.', $value)[0];
        $bytes = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($bytes === false || rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=') !== $encoded) self::fail();
        try { $claims = json_decode($bytes, true, 16, JSON_THROW_ON_ERROR); } catch (\Throwable) { self::fail(); }
        if (!is_array($claims)) self::fail();
        self::exact($claims, ['operationId', 'rootOperationId', 'instanceId', 'epoch', 'nonce']);
        self::uuid($claims['operationId']); self::uuid($claims['rootOperationId']); self::epoch($claims['epoch']);
        if (!is_string($claims['instanceId']) || !preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $claims['instanceId'])
            || !is_string($claims['nonce']) || !preg_match('/^[a-f0-9]{32}$/D', $claims['nonce'])) self::fail();
        return $claims;
    }
    public static function capability(mixed $value): void
    {
        if (!is_string($value) || strlen($value) > 2048 || !preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]{43}$/D', $value)) self::fail();
    }
    private static function fail(): never { throw new \RuntimeException('Identity admission control is unavailable.'); }
}

final class ControlRefusal extends \RuntimeException
{
    public readonly string $safeCode;
    public function __construct(string $code) { $this->safeCode = $code; parent::__construct('Identity admission was refused.'); }
}
