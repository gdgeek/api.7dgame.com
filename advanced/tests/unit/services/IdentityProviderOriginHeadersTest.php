<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityProviderClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\web\BadRequestHttpException;

class IdentityProviderOriginHeadersTest extends TestCase
{
    public function testHeadersKeepBothOriginalValuesWithoutSelectingIdentityAudience(): void
    {
        $headers = (new OriginHeaderClient())->headers([
            'origin' => 'https://WhiteLabel.example:443',
            'referer' => 'https://WhiteLabel.example/path?display=qr',
            'aud' => 'browser-selected-audience', 'frontend_domain' => 'browser-selected-domain',
        ]);
        $this->assertSame(['Origin: https://WhiteLabel.example:443',
            'Referer: https://WhiteLabel.example/path?display=qr'], $headers);
    }

    public function testAbsentHeadersAreNotInvented(): void
    {
        $this->assertSame([], (new OriginHeaderClient())->headers(['origin' => '', 'referer' => null]));
    }

    public function testSyntacticallyInvalidOriginStillReachesTheAuthorityInsteadOfFallingBackToReferer(): void
    {
        $this->assertSame(['Origin: null', 'Referer: https://allowed.example/path'],
            (new OriginHeaderClient())->headers(['origin' => 'null', 'referer' => 'https://allowed.example/path']));
    }

    public function testCurlProxyActuallyForwardsBothHeadersWithoutChangingThePayload(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        $this->assertIsResource($socket);
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $process = proc_open([PHP_BINARY, '-S', $address, __DIR__ . '/../../_data/identity-origin-provider.php'],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
        $this->assertIsResource($process);
        $oldBase = getenv('IDENTITY_AUTH_BASE_URL');
        putenv('IDENTITY_AUTH_BASE_URL=http://' . $address);
        try {
            $ready = false;
            for ($i = 0; $i < 100; ++$i) {
                $connection = @stream_socket_client('tcp://' . $address, $errno, $error, 0.03);
                if ($connection !== false) {
                    fclose($connection);
                    $ready = true;
                    break;
                }
                usleep(10000);
            }
            $this->assertTrue($ready, 'The isolated loopback fixture must start.');
            $result = (new IdentityProviderClient())->proxyAccountLifecycle('POST', '/v1/tools/user-linked',
                ['display' => 'qr'], [], ['origin' => 'https://WhiteLabel.example:443',
                    'referer' => 'https://WhiteLabel.example/path?display=qr']);
            $this->assertSame(200, $result['status']);
            $this->assertSame(['origin' => 'https://WhiteLabel.example:443',
                'referer' => 'https://WhiteLabel.example/path?display=qr', 'payload' => ['display' => 'qr']], $result['body']);
        } finally {
            putenv($oldBase === false ? 'IDENTITY_AUTH_BASE_URL' : 'IDENTITY_AUTH_BASE_URL=' . $oldBase);
            proc_terminate($process);
            proc_close($process);
        }
    }

    #[DataProvider('unsafeHeaderValues')]
    public function testUnsafeHeadersRejectTheRequestBeforeAnyRefererFallback(string $key, mixed $value): void
    {
        $this->expectException(BadRequestHttpException::class);
        (new OriginHeaderClient())->headers(array_merge(['referer' => 'https://allowed.example/path'], [$key => $value]));
    }

    public static function unsafeHeaderValues(): array
    {
        return [
            'origin CRLF injection' => ['origin', "https://bad.example\r\nX-Injected: value"],
            'referer newline injection' => ['referer', "https://bad.example/\nX-Injected: value"],
            'origin NUL' => ['origin', "https://bad.example\0"],
            'referer DEL' => ['referer', "https://bad.example/\x7f"],
            'origin oversize' => ['origin', str_repeat('x', 8193)],
            'referer oversize' => ['referer', str_repeat('x', 8193)],
            'origin multiple values' => ['origin', ['https://a.example', 'https://b.example']],
        ];
    }
}

class OriginHeaderClient extends IdentityProviderClient
{
    public function headers(array $context): array
    {
        return $this->originHeaders($context);
    }
}
