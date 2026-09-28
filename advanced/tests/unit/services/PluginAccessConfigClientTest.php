<?php

namespace tests\unit\services;

use api\modules\v1\services\PluginAccessConfigClient;
use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\web\HttpException;

final class PluginAccessConfigClientTest extends TestCase
{
    private function response(array $replace = []): string
    {
        return json_encode(['code' => 0, 'message' => 'success', 'data' => array_replace([
            'policy_version' => 1, 'id' => 'sn-management', 'enabled' => true, 'access_scope' => 'root-only',
        ], $replace)], JSON_THROW_ON_ERROR);
    }

    public function testOnlyTheFixedPublicPolicyPathReachesTheAuthority(): void
    {
        $client = new RecordedPluginAccessConfigClient(['baseUrl' => 'http://system-admin-d:8088/']);
        foreach (['root-only', 'admin-only', 'manager-only', 'auth-only'] as $scope) {
            $client->body = $this->response(['access_scope' => $scope]);
            self::assertSame(['enabled' => true, 'access_scope' => $scope], $client->read('sn-management'));
        }
        self::assertCount(4, $client->requests); // The same client never caches a decision.
        foreach ($client->requests as $request) {
            self::assertSame('http://system-admin-d:8088/api/v1/plugin/access-config/sn-management', $request);
        }
    }

    #[DataProvider('invalidRequests')]
    public function testInvalidAuthorityOrRequestNeverStartsTransport(string $base, string $id): void
    {
        $client = new RecordedPluginAccessConfigClient(['baseUrl' => $base]);
        $this->assertUnavailable(fn() => $client->read($id));
        self::assertSame([], $client->requests);
    }

    public static function invalidRequests(): array
    {
        return array_map(static fn(array $args) => $args + [1 => 'sn-management'], [
            [''], ['file:///etc/passwd'], ['ftp://system-admin-d'],
            ['http://username:password@system-admin-d:8088'],
            ['http://system-admin-d:8088/base-path'], ['http://system-admin-d:8088?other=1'],
            ['http://system-admin-d:8088#fragment'], ['http://system-admin-d:0'],
            ['http://system-admin-d:8088', '../plugins'],
            ['http://system-admin-d:8088', 'sn-management?other=1'],
        ]);
    }

    public function testMissingEnvironmentDoesNotGuessAnAuthority(): void
    {
        $before = getenv('PLUGIN_ACCESS_CONFIG_BASE_URL');
        putenv('PLUGIN_ACCESS_CONFIG_BASE_URL');
        try {
            $client = new RecordedPluginAccessConfigClient();
            $this->assertUnavailable(fn() => $client->read('sn-management'));
            self::assertSame([], $client->requests);
        } finally {
            putenv($before === false ? 'PLUGIN_ACCESS_CONFIG_BASE_URL' : 'PLUGIN_ACCESS_CONFIG_BASE_URL=' . $before);
        }
    }

    public function testNotFoundIsAClosedDecisionAndCannotReusePriorEnabledPolicy(): void
    {
        $client = new RecordedPluginAccessConfigClient(['baseUrl' => 'http://system-admin-d:8088']);
        $client->body = $this->response();
        self::assertNotNull($client->read('sn-management'));
        $client->status = 404;
        $client->body = '{}';
        self::assertNull($client->read('sn-management'));
    }

    #[DataProvider('invalidResponses')]
    public function testUnexpectedSchemaStatusAndOversizedBodiesFailClosed(int $status, string $body): void
    {
        $client = new RecordedPluginAccessConfigClient(['baseUrl' => 'http://system-admin-d:8088']);
        $client->status = $status;
        $client->body = $body;
        $this->assertUnavailable(fn() => $client->read('sn-management'));
    }

    public static function invalidResponses(): array
    {
        $valid = ['policy_version' => 1, 'id' => 'sn-management', 'enabled' => true, 'access_scope' => 'root-only'];
        $responses = [[200, '{'], [200, '[]'], [200, '"scalar"'], [200, str_repeat('x', 16385)]];
        foreach ([null, '', 'other'] as $scope) {
            $responses[] = [200, json_encode(['code' => 0, 'data' => array_replace($valid, ['access_scope' => $scope])])];
        }
        foreach ([['policy_version' => 2], ['policy_version' => '1'], ['id' => 'another-plugin'], ['enabled' => false], ['enabled' => 1]] as $replace) {
            $responses[] = [200, json_encode(['code' => 0, 'data' => array_replace($valid, $replace)])];
        }
        $responses[] = [200, json_encode(['code' => '0', 'data' => $valid])];
        foreach ([301, 302, 401, 403, 429, 500, 503] as $status) {
            $responses[] = [$status, json_encode(['code' => 0, 'data' => $valid])];
        }
        return $responses;
    }

    public function testTransportExceptionsNeverExposePrivateDiagnosticDetails(): void
    {
        $client = new RecordedPluginAccessConfigClient(['baseUrl' => 'http://system-admin-d:8088']);
        $client->failure = new \RuntimeException('private upstream diagnostic with credential');
        $this->assertUnavailable(fn() => $client->read('sn-management'));
    }

    private function assertUnavailable(callable $operation): void
    {
        try {
            $operation();
            self::fail('Expected closed dependency failure.');
        } catch (HttpException $exception) {
            self::assertInstanceOf(PluginAccessConfigUnavailableException::class, $exception);
            self::assertSame(503, $exception->statusCode);
            self::assertSame('Plugin access configuration is unavailable.', $exception->getMessage());
            self::assertNull($exception->getPrevious());
        }
    }
}

final class RecordedPluginAccessConfigClient extends PluginAccessConfigClient
{
    public int $status = 200;
    public string $body = '{}';
    public array $requests = [];
    public ?\Throwable $failure = null;

    protected function send(string $url): array
    {
        $this->requests[] = $url;
        if ($this->failure !== null) {
            throw $this->failure;
        }
        return [$this->status, $this->body];
    }
}
