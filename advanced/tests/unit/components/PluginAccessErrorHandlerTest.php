<?php

namespace tests\unit\components;

use api\modules\v1\components\ApiErrorHandler;
use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;
use PHPUnit\Framework\TestCase;
use yii\web\ErrorHandler;
use yii\web\HttpException;
use yii\web\Response;

final class PluginAccessErrorHandlerTest extends TestCase
{
    private function convert(ErrorHandler $handler, \Throwable $exception): array
    {
        $method = new \ReflectionMethod($handler, 'convertExceptionToArray');
        return $method->invoke($handler, $exception);
    }

    public function testPolicyFailureAddsStableMachineCodeToTheUnchangedYiiEnvelope(): void
    {
        $exception = new PluginAccessConfigUnavailableException();
        $baseline = $this->convert(new ErrorHandler(), $exception);
        $actual = $this->convert(new ApiErrorHandler(), $exception);
        self::assertSame($baseline + ['error_code' => 'PLUGIN_ACCESS_CONFIG_UNAVAILABLE'], $actual);
        self::assertSame(503, $actual['status']);
        self::assertSame(0, $actual['code']);
        self::assertSame('Service Unavailable', $actual['name']);
        self::assertArrayNotHasKey('previous', $actual);
        self::assertArrayNotHasKey('stack-trace', $actual);
        $response = new Response(['format' => Response::FORMAT_JSON]);
        $response->setStatusCodeByException($exception);
        self::assertSame(503, $response->statusCode);
    }

    public function testAnOrdinaryBusiness503DoesNotGainThePolicyMachineCode(): void
    {
        foreach ([new HttpException(503, 'Ordinary operation unavailable.'), new HttpException(403, 'Forbidden.')] as $exception) {
            $baseline = $this->convert(new ErrorHandler(), $exception);
            $actual = $this->convert(new ApiErrorHandler(), $exception);
            self::assertSame($baseline, $actual);
            self::assertArrayNotHasKey('error_code', $actual);
        }
    }

    public function testPublishedApiConfigurationUsesTheProtocolAwareHandler(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        self::assertSame(ApiErrorHandler::class, $config['components']['errorHandler']['class']);
        self::assertSame('site/error', $config['components']['errorHandler']['errorAction']);
    }
}
