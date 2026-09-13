<?php

namespace tests\integration;

use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use PHPUnit\Framework\TestCase;
use tests\integration\fixtures\WebMcpHttpFixture;
use Yii;
use yii\db\Connection;
use yii\db\Query;

require_once __DIR__ . '/fixtures/WebMcpHttpFixture.php';

/** Real HTTP and MySQL; never connects to an application database or uses user credentials. */
final class WebMcpHttpRecoveryTest extends TestCase
{
    private Connection $db;
    private mixed $previousDb;
    private string $directory;
    private int $port;
    private mixed $server = null;
    private ?int $proxyPid = null;

    protected function setUp(): void
    {
        if (!getenv('WEBMCP_MYSQL_TEST_DSN') || !function_exists('pcntl_fork') || !function_exists('proc_open') || !function_exists('posix_kill')) {
            $this->markTestSkipped('Requires an isolated MySQL DSN, pcntl and proc_open');
        }
        $config = WebMcpHttpFixture::databaseConfig(); // Validate before any connection or cleanup.
        unset($config['class']);
        $this->previousDb = Yii::$app->db;
        $this->db = new Connection($config);
        Yii::$app->set('db', $this->db);
        WebMcpHttpFixture::assertTestDatabase($this->db);
        foreach (['verse', 'verse_code', 'webmcp_operation', 'webmcp_test_write_counter', 'group_verse', 'group_user'] as $table) {
            $this->db->createCommand("DROP TABLE IF EXISTS $table")->execute();
        }
        $this->db->createCommand('CREATE TABLE verse (id INT PRIMARY KEY, author_id INT, name TEXT, data JSON) ENGINE=InnoDB')->execute();
        $this->db->createCommand('CREATE TABLE verse_code (id INT PRIMARY KEY, verse_id INT, code_id INT, blockly TEXT, js TEXT, lua TEXT) ENGINE=InnoDB')->execute();
        $this->db->createCommand('CREATE TABLE group_verse (group_id INT, verse_id INT) ENGINE=InnoDB')->execute();
        $this->db->createCommand('CREATE TABLE group_user (group_id INT, user_id INT) ENGINE=InnoDB')->execute();
        $this->db->createCommand('CREATE TABLE webmcp_test_write_counter (id INT PRIMARY KEY, commits INT NOT NULL) ENGINE=InnoDB')->execute();
        $this->db->createCommand()->insert('webmcp_test_write_counter', ['id' => 1, 'commits' => 0])->execute();
        $this->db->createCommand()->insert('verse', ['id' => 1, 'author_id' => 7, 'name' => 'original', 'data' => '{}'])->execute();
        require_once dirname(__DIR__, 2) . '/console/migrations/m260911_230000_add_webmcp_write_receipts.php';
        ob_start();
        try {
            (new \m260911_230000_add_webmcp_write_receipts(['db' => $this->db, 'compact' => true]))->safeUp();
        } finally {
            ob_end_clean();
        }
        $this->directory = sys_get_temp_dir() . '/webmcp-http-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
        $listener = self::listen();
        $this->port = self::portOf($listener);
        fclose($listener);
        // Explicit environment contains only disposable DB credentials; inherit no application secrets.
        $environment = [
            'WEBMCP_MYSQL_TEST_DSN' => $config['dsn'],
            'WEBMCP_MYSQL_TEST_USER' => $config['username'],
            'WEBMCP_MYSQL_TEST_PASSWORD' => $config['password'],
        ];
        $this->server = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $this->port, __DIR__ . '/fixtures/webmcp-http-router.php'],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', $this->directory . '/server.log', 'a'], 2 => ['file', $this->directory . '/server.log', 'a']],
            $pipes, dirname(__DIR__, 2), $environment);
        $this->assertIsResource($this->server);
        $deadline = microtime(true) + 10;
        do {
            $ready = $this->request('GET', '/ready');
            if ($ready['status'] === 200) return;
            usleep(50000);
        } while (microtime(true) < $deadline && proc_get_status($this->server)['running']);
        $this->fail('Isolated HTTP fixture did not become ready: ' . json_encode($ready));
    }

    protected function tearDown(): void
    {
        if ($this->proxyPid !== null) {
            posix_kill($this->proxyPid, SIGTERM);
            pcntl_waitpid($this->proxyPid, $status);
        }
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        if (isset($this->previousDb)) Yii::$app->set('db', $this->previousDb);
        if (isset($this->db)) $this->db->close();
        if (isset($this->directory)) {
            foreach (glob($this->directory . '/*') as $file) unlink($file);
            rmdir($this->directory);
        }
    }

    public function testCommittedWriteWithLostHttpResponseRecoversAndReplaysExactlyOnce(): void
    {
        $operation = ReliableWrite::uuid();
        $revision = Verse::findOne(1)->serverRevision;
        $headers = ['Idempotency-Key' => $operation, 'If-Match' => '"' . $revision . '"'];
        $body = ['name' => 'committed despite lost response'];
        $listener = self::listen();
        $proxyPort = self::portOf($listener);
        $this->db->close(); // Never share a live PDO socket with the forked fault proxy.
        $pid = pcntl_fork();
        if ($pid === -1) throw new \RuntimeException('Cannot start fault proxy');
        if ($pid === 0) {
            $this->dropSuccessfulResponse($listener);
            exit(0);
        }
        $this->proxyPid = $pid;
        fclose($listener);
        $lost = $this->request('PUT', '/v1/verses/1', $body, $headers, $proxyPort);
        pcntl_waitpid($pid, $status);
        $this->proxyPid = null;
        $this->assertTrue(pcntl_wifexited($status));
        $this->assertSame(0, pcntl_wexitstatus($status));
        $this->assertSame(0, $lost['status']);
        $this->assertSame('', $lost['wire'], 'Client must receive no HTTP success bytes');
        $evidence = json_decode(file_get_contents($this->directory . '/drop.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(200, $evidence['upstreamStatus']);
        $this->assertTrue($evidence['completeBody']);
        $this->assertSame(0, $evidence['forwardedBytes']);
        $this->assertSame($operation, $evidence['receipt']['operationId']);
        $receipt = $this->request('GET', '/v1/verses/1/operations/' . $operation);
        $this->assertSame(200, $receipt['status']);
        $this->assertEquals($evidence['receipt'], $receipt['json']);
        $this->assertSame('completed', $receipt['json']['status']);
        $this->assertSame($body['name'], Verse::findOne(1)->name);
        $this->assertSame(Verse::findOne(1)->serverRevision, $receipt['json']['serverRevision']);
        $replay = $this->request('PUT', '/v1/verses/1', $body, $headers);
        $this->assertSame(200, $replay['status']);
        $this->assertTrue($replay['json']['replayed']);
        $this->assertSame($receipt['json'], $replay['json']['writeReceipt']);
        $this->assertSingleWrite();
    }

    public function testPublicationSurvivesLostHttpResponseWithOneFixedArchive(): void
    {
        require_once __DIR__ . '/fixtures/PublicationFixture.php';
        \tests\integration\fixtures\PublicationFixture::reset($this->db);
        $operation = ReliableWrite::uuid();
        $headers = ['Idempotency-Key' => $operation, 'If-Match' => '"' . Verse::findOne(1)->serverRevision . '"'];
        $listener = self::listen(); $port = self::portOf($listener); $this->db->close();
        $pid = pcntl_fork();
        if ($pid === 0) { $this->dropSuccessfulResponse($listener); exit(0); }
        $this->proxyPid = $pid; fclose($listener);
        $lost = $this->request('POST', '/v1/verses/1/take-photo', [], $headers, $port);
        pcntl_waitpid($pid, $status); $this->proxyPid = null;
        $this->assertSame(0, pcntl_wexitstatus($status));
        $this->assertSame('', $lost['wire']);
        $receipt = $this->request('GET', '/v1/verses/1/operations/' . $operation);
        $this->assertSame(200, $receipt['status']);
        $version = $receipt['json']['publicationVersionId'];
        $read = $this->request('GET', '/v1/verses/1/publications/' . $version);
        $this->assertSame(200, $read['status']);
        $this->assertSame($receipt['json']['contentHash'], 'sha256:' . hash('sha256', $read['json']['canonicalBody']));
        $replay = $this->request('POST', '/v1/verses/1/take-photo', [], $headers);
        $this->assertSame(200, $replay['status']);
        $this->assertTrue($replay['json']['replayed']);
        $this->assertSame($version, $replay['json']['publicationVersionId']);
        $this->assertSame(1, (int) (new Query())->from('scene_publication_revision')->count());
        $this->assertSame(1, (int) (new Query())->from('snapshot')->count());
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }

    public function testStaleHttpWriterCannotOverwriteCommittedContent(): void
    {
        $revision = Verse::findOne(1)->serverRevision;
        $first = $this->request('PUT', '/v1/verses/1', ['name' => 'newer'],
            ['Idempotency-Key' => ReliableWrite::uuid(), 'If-Match' => '"' . $revision . '"']);
        $this->assertSame(200, $first['status']);
        $stale = $this->request('PUT', '/v1/verses/1', ['name' => 'stale'],
            ['Idempotency-Key' => ReliableWrite::uuid(), 'If-Match' => '"' . $revision . '"']);
        $this->assertSame(409, $stale['status']);
        $this->assertSame('newer', Verse::findOne(1)->name);
        $this->assertSingleWrite();
    }

    public function testHttpReceiptRequiresOriginalActorAndCurrentPermission(): void
    {
        $operation = ReliableWrite::uuid();
        $revision = Verse::findOne(1)->serverRevision;
        $saved = $this->request('PUT', '/v1/verses/1', ['name' => 'private receipt'],
            ['Idempotency-Key' => $operation, 'If-Match' => '"' . $revision . '"']);
        $this->assertSame(200, $saved['status']);
        // Test-only fixture ownership transfer: actor 8 can edit but cannot read actor 7's receipt.
        $this->db->createCommand()->update('verse', ['author_id' => 8], ['id' => 1])->execute();
        $path = '/v1/verses/1/operations/' . $operation;
        $otherActor = $this->request('GET', $path, null, ['X-WebMCP-Test-Actor' => '8']);
        $this->assertSame(404, $otherActor['status']);
        $revoked = $this->request('GET', $path);
        $this->assertSame(403, $revoked['status']);
        $this->db->createCommand()->update('verse', ['author_id' => 7], ['id' => 1])->execute();
        $restored = $this->request('GET', $path);
        $this->assertSame(200, $restored['status']);
        $this->assertEquals($saved['json']['writeReceipt'], $restored['json']);
        $this->assertSingleWrite();
    }

    private function assertSingleWrite(): void
    {
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        // Counting receipts alone could miss duplicated mutations before a single receipt.
        $this->assertSame(1, (int) $this->db->createCommand('SELECT commits FROM webmcp_test_write_counter WHERE id=1')->queryScalar());
    }

    private static function listen(): mixed
    {
        $listener = stream_socket_server('tcp://127.0.0.1:0', $error, $message);
        if ($listener === false) throw new \RuntimeException('Cannot bind loopback test listener');
        return $listener;
    }

    private static function portOf(mixed $listener): int
    {
        return (int) substr(strrchr(stream_socket_get_name($listener, false), ':'), 1);
    }

    private function request(string $method, string $path, ?array $body = null, array $headers = [], ?int $port = null): array
    {
        $socket = @stream_socket_client('tcp://127.0.0.1:' . ($port ?? $this->port), $error, $message, 1);
        if ($socket === false) return ['status' => 0, 'json' => null, 'wire' => ''];
        stream_set_timeout($socket, 10);
        $payload = $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR);
        $request = "$method $path HTTP/1.1\r\nHost: 127.0.0.1\r\nConnection: close\r\nContent-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n";
        foreach ($headers as $name => $value) $request .= "$name: $value\r\n";
        self::writeAll($socket, $request . "\r\n" . $payload);
        $wire = stream_get_contents($socket);
        $timedOut = stream_get_meta_data($socket)['timed_out'];
        fclose($socket);
        if ($timedOut) throw new \RuntimeException('Loopback HTTP fixture timed out');
        return self::parseResponse($wire);
    }

    private static function parseResponse(string $wire): array
    {
        preg_match('~\AHTTP/1\.[01] ([0-9]{3})~', $wire, $match);
        $body = explode("\r\n\r\n", $wire, 2)[1] ?? '';
        return ['status' => isset($match[1]) ? (int) $match[1] : 0,
            'json' => $body === '' ? null : json_decode($body, true, 512, JSON_THROW_ON_ERROR), 'wire' => $wire];
    }

    private static function writeAll(mixed $socket, string $bytes): void
    {
        while ($bytes !== '') {
            $written = fwrite($socket, $bytes);
            if ($written === false || $written === 0) throw new \RuntimeException('Loopback socket write failed');
            $bytes = substr($bytes, $written);
        }
    }

    private function dropSuccessfulResponse(mixed $listener): void
    {
        $client = stream_socket_accept($listener, 10);
        fclose($listener);
        if ($client === false) exit(2);
        stream_set_timeout($client, 10);
        $request = '';
        while (!str_contains($request, "\r\n\r\n")) {
            $part = fread($client, 8192);
            if ($part === '' || $part === false) exit(3);
            $request .= $part;
        }
        [$head, $body] = explode("\r\n\r\n", $request, 2);
        preg_match('/\r\nContent-Length: ([0-9]+)/i', $head, $match);
        $length = (int) ($match[1] ?? 0);
        while (strlen($body) < $length) {
            $part = fread($client, $length - strlen($body));
            if ($part === '' || $part === false) exit(4);
            $body .= $part;
        }
        $upstream = stream_socket_client('tcp://127.0.0.1:' . $this->port, $error, $message, 2);
        if ($upstream === false) exit(5);
        stream_set_timeout($upstream, 10);
        self::writeAll($upstream, $head . "\r\n\r\n" . $body);
        $wire = stream_get_contents($upstream);
        fclose($upstream);
        $response = self::parseResponse($wire);
        [$responseHead, $responseBody] = array_pad(explode("\r\n\r\n", $wire, 2), 2, '');
        preg_match('/\r\nContent-Length: ([0-9]+)/i', $responseHead, $match);
        if ($response['status'] !== 200 || !isset($match[1]) || strlen($responseBody) !== (int) $match[1]
            || ($response['json']['writeReceipt']['status'] ?? null) !== 'completed') exit(6);
        file_put_contents($this->directory . '/drop.json', json_encode([
            'upstreamStatus' => 200, 'completeBody' => true, 'forwardedBytes' => 0,
            'receipt' => $response['json']['writeReceipt'],
        ], JSON_THROW_ON_ERROR));
        // Only after upstream success has fully arrived, lose every response byte to the caller.
        fclose($client);
    }
}
