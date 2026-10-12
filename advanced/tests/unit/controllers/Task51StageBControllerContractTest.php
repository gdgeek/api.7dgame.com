<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\Task51StageBController;
use api\modules\v1\services\Task51CanonicalArtifact;
use api\modules\v1\services\Task51CoordinatorException;
use api\modules\v1\services\Task51StageBCoordinatorService;
use api\modules\v1\services\Task51StageBSettings;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Response;
use yii\web\ConflictHttpException;

require_once dirname(__DIR__, 2) . '/support/Task51ArtifactFixture.php';

final class Task51StageBControllerContractTest extends TestCase
{
    public function testPublishedConfigExposesOnlyExactPostControlRoutes(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];

        $this->assertSame('v1/task51-stage-b/issue', $rules['POST v1/task51/stage-b/issue'] ?? null);
        $this->assertSame('v1/task51-stage-b/claim', $rules['POST v1/task51/stage-b/claim'] ?? null);
        $this->assertSame('v1/task51-stage-b/consume', $rules['POST v1/task51/stage-b/consume'] ?? null);
        foreach (array_keys($rules) as $pattern) {
            if (is_string($pattern) && str_contains($pattern, 'task51/stage-b')) {
                $this->assertStringStartsWith('POST ', $pattern);
            }
        }

        foreach ($config['components']['log']['targets'] as $target) {
            $this->assertSame([], $target['logVars'] ?? null, 'API logs must not append request globals.');
        }
        $coordinatorDb = $config['components']['task51CoordinatorDb'] ?? null;
        $this->assertIsArray($coordinatorDb);
        $this->assertSame(\yii\db\Connection::class, $coordinatorDb['class']);
        $this->assertSame(\yii\db\Command::class, $coordinatorDb['commandClass']);
        $this->assertFalse($coordinatorDb['enableSlaves']);
        $this->assertSame(5, $coordinatorDb['attributes'][\PDO::ATTR_TIMEOUT] ?? null);
        $this->assertNotSame(\common\components\CynosDbConnection::class, $coordinatorDb['class']);
        $this->assertSame(\yii\filters\Cors::class, $config['as cors']['class'] ?? null);
        $allowedHeaders = $config['as cors']['cors']['Access-Control-Request-Headers'] ?? [];
        $this->assertNotContains('X-Task51-Claim-Capability', $allowedHeaders);
        $this->assertNotContains('X-Task51-Internal-Token', $allowedHeaders);
    }

    public function testControllerPublishesExactVerbsAndSecurityBoundaries(): void
    {
        $controller = new Task51StageBController('task51-stage-b', Yii::$app);
        $verbs = (new \ReflectionMethod($controller, 'verbs'))->invoke($controller);
        $this->assertSame(['POST'], $verbs['issue']);
        $this->assertSame(['POST'], $verbs['claim']);
        $this->assertSame(['POST'], $verbs['consume']);

        $source = file_get_contents((new \ReflectionClass($controller))->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString("CLAIM_ORIGIN", $source);
        $this->assertStringContainsString("'X-Task51-Claim-Capability'", $source);
        $this->assertStringContainsString('requiredRawBody(Task51CanonicalArtifact::MAX_STAGE_B_BYTES)', $source);
        $this->assertStringContainsString('requiredRawBody(Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES)', $source);
        $this->assertStringContainsString("headers->get('Content-Length')", $source);
        $this->assertStringContainsString('hash_equals($configured, $provided)', $source);
        $this->assertStringContainsString("no-store, private", $source);
        $this->assertStringNotContainsString('Yii::error', $source);
        $this->assertStringNotContainsString('Yii::info', $source);
        $this->assertStringNotContainsString('Yii::warning', $source);
        $this->assertStringNotContainsString('getBodyParams()', $source);
        $this->assertStringContainsString("get('task51CoordinatorDb')", $source);
        $this->assertStringContainsString('get_class($db) !== Connection::class', $source);
        $this->assertStringContainsString('$db->commandClass !== Command::class', $source);
        $this->assertStringContainsString('$db->enableSlaves !== false', $source);
        $this->assertStringNotContainsString('new DbTask51StageBRepository(Yii::$app->db)', $source);
    }

    public function testFeatureDefaultsOffAndMissingTokenHasNoFallback(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/api/modules/v1/services/Task51StageBSettings.php');
        $this->assertIsString($source);
        $this->assertStringContainsString("getenv('TASK51_STAGE_B_COORDINATOR_ENABLED')", $source);
        $this->assertStringContainsString("TASK51_STAGE_B_INTERNAL_TOKEN", $source);
        $this->assertStringContainsString("TASK51_STAGE_B_COORDINATOR_PUBLIC_ORIGIN", $source);
        $this->assertStringContainsString('isExact256BitBase64Url', $source);
        $this->assertStringContainsString("https://d.xrugc.com", $source);
        $this->assertStringContainsString("https://api.xrteeth.com", $source);
        $this->assertStringNotContainsString('IDENTITY_INTERNAL_API_TOKEN', $source);
        $this->assertStringContainsString('$this->internalToken() !== null', $source);
        $this->assertStringContainsString('$this->coordinatorPublicOrigin() !== null', $source);
        $this->assertStringContainsString('$this->embeddedServerPublishSha()', $source);
        $this->assertStringContainsString('$this->isProductionRuntime()', $source);
    }

    public function testServerPublishIdentityMustMatchImmutableImageCommit(): void
    {
        $releaseRoot = sys_get_temp_dir() . '/task51-release-' . bin2hex(random_bytes(8));
        $previous = getenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA');
        $publishSha = str_repeat('a', 40);
        try {
            $this->assertTrue(mkdir($releaseRoot, 0700));
            putenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA=' . $publishSha);
            $settings = new Task51StageBSettings($releaseRoot);

            $this->assertNull($settings->serverPublishSha());
            file_put_contents($releaseRoot . '/GIT_COMMIT', $publishSha . "\n", LOCK_EX);
            $this->assertSame($publishSha, $settings->serverPublishSha());

            putenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA=' . str_repeat('b', 40));
            $this->assertNull($settings->serverPublishSha());
            file_put_contents($releaseRoot . '/GIT_COMMIT', $publishSha . "\nextra", LOCK_EX);
            putenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA=' . $publishSha);
            $this->assertNull($settings->serverPublishSha());
        } finally {
            if ($previous === false) {
                putenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA');
            } else {
                putenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA=' . $previous);
            }
            @unlink($releaseRoot . '/GIT_COMMIT');
            @rmdir($releaseRoot);
        }
    }

    public function testInternalTokenRejectsNonCanonicalBase64UrlPadBits(): void
    {
        $previous = getenv('TASK51_STAGE_B_INTERNAL_TOKEN');
        try {
            putenv('TASK51_STAGE_B_INTERNAL_TOKEN=' . \tests\support\Task51ArtifactFixture::CAPABILITY);
            $this->assertSame(
                \tests\support\Task51ArtifactFixture::CAPABILITY,
                (new Task51StageBSettings())->internalToken()
            );
            $nonCanonical = substr(\tests\support\Task51ArtifactFixture::CAPABILITY, 0, -1) . 'N';
            putenv('TASK51_STAGE_B_INTERNAL_TOKEN=' . $nonCanonical);
            $this->assertNull((new Task51StageBSettings())->internalToken());
        } finally {
            if ($previous === false) {
                putenv('TASK51_STAGE_B_INTERNAL_TOKEN');
            } else {
                putenv('TASK51_STAGE_B_INTERNAL_TOKEN=' . $previous);
            }
        }
    }

    public function testOnlyExactCanonicalCoordinatorHostCanBeReady(): void
    {
        $settings = new Task51StageBSettings();

        $this->assertTrue($settings->isCanonicalRequestHost('api.xrteeth.com'));
        $this->assertFalse($settings->isCanonicalRequestHost('api.tmrpp.com'));
        $this->assertFalse($settings->isCanonicalRequestHost('API.XRTEETH.COM'));
        $this->assertFalse($settings->isCanonicalRequestHost('api.xrteeth.com:443'));
        $this->assertFalse($settings->isCanonicalRequestHost('api.xrteeth.com.'));
        $this->assertFalse($settings->isCanonicalRequestHost(null));

        $controllerSource = file_get_contents((new \ReflectionClass(Task51StageBController::class))->getFileName());
        $this->assertIsString($controllerSource);
        $this->assertStringContainsString("request->headers->get('Host')", $controllerSource);
        $this->assertStringContainsString('isCanonicalRequestHost', $controllerSource);
        $this->assertStringContainsString("hasModule('debug')", $controllerSource);
        $this->assertStringContainsString("hasModule('gii')", $controllerSource);
    }

    public function testEntryDefaultsProductionAndStopsBeforeDebugCanReadTask51Secrets(): void
    {
        $settings = new Task51StageBSettings();

        $this->assertFalse($settings->isProductionRuntime());
        $entrySource = file_get_contents(dirname(__DIR__, 3) . '/api/web/index.php');
        $this->assertIsString($entrySource);
        $this->assertStringContainsString("\$yiiEnvironmentValue === '' ? 'prod'", $entrySource);
        $this->assertStringContainsString('$yiiDebugIsValid', $entrySource);
        $this->assertStringContainsString('$yiiEnvironmentIsValid', $entrySource);
        $this->assertStringContainsString('$task51EnabledIsValid', $entrySource);
        $this->assertStringContainsString('$task51SensitiveConfigurationPresent', $entrySource);
        $this->assertStringContainsString('$task51RequestPath', $entrySource);
        $this->assertStringContainsString("str_starts_with(\$task51RequestPath, '/v1/task51/stage-b/')", $entrySource);
        $this->assertStringContainsString("'/index.php/v1/task51/stage-b'", $entrySource);
        $this->assertStringContainsString('$task51NonCanonicalFrontControllerRequest', $entrySource);
        $this->assertStringContainsString("getenv('TASK51_STAGE_B_COORDINATOR_ENABLED')", $entrySource);
        $this->assertStringContainsString("!\$yiiDebugIsValid || !\$yiiEnvironmentIsValid", $entrySource);
        $this->assertStringContainsString("YII_DEBUG !== false || YII_ENV !== 'prod'", $entrySource);
        $this->assertStringContainsString('exit;', $entrySource);
        $this->assertStringNotContainsString("define('YII_DEBUG', true)", $entrySource);
        $this->assertStringNotContainsString("define('YII_ENV', 'dev')", $entrySource);
    }

    public function testPredefinedDebugConstantsStopBeforeBootstrapWithNoStore404(): void
    {
        if (!function_exists('proc_open') || !function_exists('stream_socket_server')) {
            $this->markTestSkipped('Entry subprocess smoke requires proc_open and TCP sockets.');
        }

        $fixtureRoot = sys_get_temp_dir() . '/task51-entry-' . bin2hex(random_bytes(8));
        $process = null;
        $pipes = [];
        try {
            foreach ([
                $fixtureRoot . '/api/web',
                $fixtureRoot . '/vendor/yiisoft/yii2',
                $fixtureRoot . '/common/config',
                $fixtureRoot . '/api/config',
            ] as $directory) {
                $this->assertTrue(mkdir($directory, 0700, true));
            }
            $entrySource = file_get_contents(dirname(__DIR__, 3) . '/api/web/index.php');
            $this->assertIsString($entrySource);
            file_put_contents($fixtureRoot . '/api/web/index.php', $entrySource, LOCK_EX);
            file_put_contents(
                $fixtureRoot . '/vendor/autoload.php',
                "<?php file_put_contents(dirname(__DIR__) . '/bootstrap-sentinel', 'autoload');\n",
                LOCK_EX
            );
            file_put_contents(
                $fixtureRoot . '/prepend.php',
                <<<'PHP'
<?php
putenv('YII_DEBUG=0');
putenv('YII_ENV=prod');
putenv('TASK51_STAGE_B_COORDINATOR_ENABLED=1');
define('YII_DEBUG', true);
define('YII_ENV', 'dev');
PHP,
                LOCK_EX
            );
            file_put_contents(
                $fixtureRoot . '/router.php',
                "<?php require __DIR__ . '/api/web/index.php';\n",
                LOCK_EX
            );

            $socket = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
            $this->assertIsResource($socket, $errorMessage);
            $socketName = stream_socket_get_name($socket, false);
            fclose($socket);
            $this->assertIsString($socketName);
            $port = (int)substr((string)strrchr($socketName, ':'), 1);
            $this->assertGreaterThan(0, $port);

            $process = proc_open([
                PHP_BINARY,
                '-d',
                'auto_prepend_file=' . $fixtureRoot . '/prepend.php',
                '-S',
                '127.0.0.1:' . $port,
                $fixtureRoot . '/router.php',
            ], [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes, $fixtureRoot);
            $this->assertIsResource($process);

            $responseHeaders = [];
            $deadline = microtime(true) + 5.0;
            do {
                $http_response_header = [];
                @file_get_contents(
                    'http://127.0.0.1:' . $port . '/v1/task51/stage-b/claim',
                    false,
                    stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 0.25]])
                );
                if ($http_response_header !== []) {
                    $responseHeaders = $http_response_header;
                    break;
                }
                usleep(50_000);
            } while (microtime(true) < $deadline);

            $this->assertNotEmpty($responseHeaders);
            $this->assertStringContainsString(' 404 ', $responseHeaders[0]);
            $this->assertContains('Cache-Control: no-store, private, max-age=0', $responseHeaders);
            $this->assertFileDoesNotExist($fixtureRoot . '/bootstrap-sentinel');
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                foreach ($pipes as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }
                proc_close($process);
            }
            $this->removeTestDirectory($fixtureRoot);
        }
    }

    public function testReleaseCopiesTheExactGuardedEntrypoint(): void
    {
        $advancedEntry = file_get_contents(dirname(__DIR__, 3) . '/api/web/index.php');
        $releaseEntry = file_get_contents(dirname(__DIR__, 4) . '/files/api/web/index.php');
        $dockerfile = file_get_contents(dirname(__DIR__, 4) . '/docker/Release');

        $this->assertIsString($advancedEntry);
        $this->assertIsString($releaseEntry);
        $this->assertIsString($dockerfile);
        $this->assertSame($advancedEntry, $releaseEntry);
        $this->assertStringContainsString(
            'COPY files/api/web/index.php /var/www/html/advanced/api/web/',
            $dockerfile
        );
    }

    public function testReleaseUsesProductionIniAndExplicitTraceRedaction(): void
    {
        $root = dirname(__DIR__, 4);
        $dockerfile = file_get_contents($root . '/docker/Release');
        $securityIni = file_get_contents($root . '/docker/task51-security.ini');

        $this->assertIsString($dockerfile);
        $this->assertIsString($securityIni);
        $this->assertStringContainsString('php.ini-production', $dockerfile);
        $this->assertStringNotContainsString('php.ini-development', $dockerfile);
        $this->assertStringContainsString(
            'COPY ./docker/task51-security.ini $PHP_INI_DIR/conf.d/zz-task51-security.ini',
            $dockerfile
        );
        foreach ([
            'display_errors=Off',
            'display_startup_errors=Off',
            'zend.exception_ignore_args=On',
            'log_errors=On',
        ] as $directive) {
            $this->assertStringContainsString($directive, $securityIni);
        }
    }

    public function testReleaseApacheNamesCanonicalCoordinatorVhostExplicitly(): void
    {
        $apache = file_get_contents(dirname(__DIR__, 4) . '/docker/000-default.conf');

        $this->assertIsString($apache);
        $this->assertMatchesRegularExpression(
            '/<VirtualHost \*:80>.*?ServerName api\.xrteeth\.com'
                . '.*?ServerAlias api\.xrugc\.com.*?DocumentRoot \/var\/www\/html\/advanced\/api\/web/s',
            $apache
        );
        $this->assertStringContainsString(
            '<LocationMatch "^/(index\\.php/)?v1/task51/stage-b/(issue|claim|consume)$">',
            $apache
        );
        $this->assertStringContainsString('LimitRequestBody 16384', $apache);
    }

    public function testRawArtifactAndCapabilityTraceBoundariesAreSensitive(): void
    {
        foreach ([
            [Task51StageBCoordinatorService::class, 'issue', ['rawStageB', 'claimCapability']],
            [Task51StageBCoordinatorService::class, 'claim', ['rawStageB', 'claimCapability']],
            [Task51StageBCoordinatorService::class, 'consume', ['rawRunnerExport']],
            [Task51StageBCoordinatorService::class, 'capabilityHash', ['capability']],
            [Task51StageBCoordinatorService::class, 'assertDeploymentBinding', ['stageB']],
            [Task51StageBCoordinatorService::class, 'assertIssueWindow', ['stageB']],
            [Task51StageBCoordinatorService::class, 'assertStoredBinding', ['stageB']],
            [Task51CanonicalArtifact::class, 'parseStageB', ['raw']],
            [Task51CanonicalArtifact::class, 'parseRunnerExport', ['raw']],
            [Task51CanonicalArtifact::class, 'parseCanonical', ['raw']],
            [Task51CanonicalArtifact::class, 'sha256', ['raw']],
            [Task51CanonicalArtifact::class, 'encode', ['value']],
            [Task51CanonicalArtifact::class, 'canonicalCopy', ['value']],
            [Task51StageBSettings::class, 'isExact256BitBase64Url', ['value']],
        ] as [$class, $method, $parameters]) {
            $reflection = new \ReflectionMethod($class, $method);
            $parametersByName = [];
            foreach ($reflection->getParameters() as $candidate) {
                $parametersByName[$candidate->getName()] = $candidate;
            }
            foreach ($parameters as $parameterName) {
                $this->assertArrayHasKey($parameterName, $parametersByName);
                $parameter = $parametersByName[$parameterName];
                $this->assertNotEmpty(
                    $parameter->getAttributes(\SensitiveParameter::class),
                    $class . '::' . $method . '($' . $parameterName
                        . ') must mask exception trace arguments.'
                );
            }
        }
    }

    public function testSensitiveResponsesReceivePrivateNoStoreHeaders(): void
    {
        $original = Yii::$app->get('response', false);
        Yii::$app->set('response', new Response());
        try {
            $controller = new Task51StageBController('task51-stage-b', Yii::$app);
            $method = new \ReflectionMethod($controller, 'applySensitiveResponseHeaders');
            $method->invoke($controller);

            $this->assertSame('no-store, private, max-age=0', Yii::$app->response->headers->get('Cache-Control'));
            $this->assertSame('no-cache', Yii::$app->response->headers->get('Pragma'));
            $this->assertSame('nosniff', Yii::$app->response->headers->get('X-Content-Type-Options'));
        } finally {
            Yii::$app->set('response', $original);
        }
    }

    public function testCoordinatorConflictMapsTo409WithoutReceiptBody(): void
    {
        $controller = new Task51StageBController('task51-stage-b', Yii::$app);
        $method = new \ReflectionMethod($controller, 'throwHttpException');

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('exact-one transition was rejected');
        $method->invoke($controller, new Task51CoordinatorException(
            Task51CoordinatorException::CONFLICT,
            'internal state detail that must not be returned'
        ));
    }

    private function removeTestDirectory(string $root): void
    {
        if (!is_dir($root)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $entry) {
            if ($entry->isDir()) {
                rmdir($entry->getPathname());
            } else {
                unlink($entry->getPathname());
            }
        }
        rmdir($root);
    }
}
