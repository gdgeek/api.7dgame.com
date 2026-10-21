<?php

declare(strict_types=1);

$runnerClass = (string) getenv('ADMISSION_FIXTURE_RUNNER_CLASS');
if (!in_array($runnerClass, ['common\\components\\admission\\HttpAdmission', 'app\\components\\admission\\HttpAdmission'], true)) throw new RuntimeException('Fixture class required.');
if (!class_exists($runnerClass, false)) {
    $_SERVER['IDENTITY_DEV_ADMISSION_KEY'] = getenv('IDENTITY_DEV_ADMISSION_KEY');
    $_ENV['IDENTITY_DEV_ADMISSION_KEY'] = getenv('IDENTITY_DEV_ADMISSION_KEY');
}
if (!in_array(strtolower(trim((string) getenv('IDENTITY_DEV_ADMISSION_ENABLED'))), ['', '0', 'false', 'off'], true)) {
    require_once getenv('ADMISSION_FIXTURE_RUNNER_FILE');
    if (!$runnerClass::isRunning()) { $runnerClass::runEntry(__FILE__); return; }
}
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
require getenv('ADMISSION_FIXTURE_VENDOR') . '/autoload.php';
require getenv('ADMISSION_FIXTURE_VENDOR') . '/yiisoft/yii2/Yii.php';
if (!function_exists('fixtureTrace')) {
    function fixtureTrace(string $event): void { file_put_contents(getenv('ADMISSION_FIXTURE_TRACE'), $event . "\n", FILE_APPEND | LOCK_EX); }
}
$case = (string) ($_GET['case'] ?? 'normal');
$GLOBALS['admissionFixtureCase'] = $case;
if (in_array($case, ['session-delay', 'session-fail'], true)) {
    session_set_save_handler(new class($case) implements SessionHandlerInterface {
        public function __construct(private string $case) {}
        public function open(string $path, string $name): bool { return true; }
        public function close(): bool { return true; }
        public function read(string $id): string|false { return ''; }
        public function write(string $id, string $data): bool {
            fixtureTrace('session-write-start'); usleep(200000);
            if ($this->case === 'session-fail') return false;
            file_put_contents(getenv('ADMISSION_FIXTURE_BASE') . '/sessions/custom', $data); fixtureTrace('session-write-done'); return true;
        }
        public function destroy(string $id): bool { return true; }
        public function gc(int $max_lifetime): int|false { return 0; }
    }, true);
}
class FixtureController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    public function actionIndex(): string
    {
        $case = $GLOBALS['admissionFixtureCase'];
        fixtureTrace('handler-start'); \Yii::$app->session->set('marker', 'saved');
        if ($case === 'exit') { echo 'raw-exit'; exit; }
        if ($case === 'fatal') { trigger_error('Synthetic fixture fatal.', E_USER_ERROR); }
        if ($case === 'throw') throw new RuntimeException('Synthetic fixture throw.');
        if ($case === 'caught-work') {
            $runner = getenv('ADMISSION_FIXTURE_RUNNER_CLASS');
            try { $runner::work(static function (): void { throw new RuntimeException('Synthetic side-effect ACK failure.'); }); } catch (Throwable) { fixtureTrace('caught-work-error'); }
        }
        if (in_array($case, ['transaction-open', 'transaction-commit'], true)) {
            $transaction = \Yii::$app->db->beginTransaction(); \Yii::$app->db->createCommand('CREATE TABLE fixture (id INTEGER)')->execute();
            \Yii::$app->db->createCommand('INSERT INTO fixture VALUES (1)')->execute();
            if ($case === 'transaction-commit') $transaction->commit();
            fixtureTrace($case);
        }
        if ($case === 'custom-error') set_error_handler(static fn() => true);
        if ($case === 'shutdown-session') register_shutdown_function(static function (): void {
            session_start(['use_cookies' => 0, 'cache_limiter' => '']); $_SESSION['late'] = 'saved'; fixtureTrace('late-session');
        });
        if ($case === 'shutdown-work') register_shutdown_function(static function (): void {
            usleep(150000); file_put_contents(getenv('ADMISSION_FIXTURE_BASE') . '/shutdown-work', 'finished'); fixtureTrace('shutdown-work-done');
        });
        if (in_array($case, ['long-no-child', 'child-before-expired'], true)) usleep(180000);
        if ($case === 'abort') { usleep(250000); fixtureTrace('abort-work-done'); }
        if (in_array($case, ['child-late', 'child-hang', 'child-before-expired'], true)) {
            $runner = getenv('ADMISSION_FIXTURE_RUNNER_CLASS');
            $headers = $runner::continuationHeaders('identity-old'); $lines = ['Connection: close'];
            foreach ($headers as $name => $value) $lines[] = $name . ': ' . $value;
            file_get_contents(getenv('ADMISSION_FIXTURE_CHILD_URL'), false, stream_context_create(['http' => ['header' => implode("\r\n", $lines), 'timeout' => 1]]));
            fixtureTrace('child-http-response');
        }
        if (in_array($case, ['end', 'swallowed-end', 'repeated-end'], true)) {
            try {
                \Yii::$app->response->data = 'ended';
                if ($case === 'repeated-end') {
                    try { \Yii::$app->end(); } catch (Throwable) { fixtureTrace('first-end-caught'); }
                    \Yii::$app->end();
                } elseif ($case === 'swallowed-end') { try { \Yii::$app->end(); } catch (Throwable) { fixtureTrace('swallowed-end'); } }
                else \Yii::$app->end();
            } finally { usleep(100000); \Yii::$app->session->set('finally', 'saved'); fixtureTrace('business-finally'); }
        }
        fixtureTrace('handler-return'); return 'normal';
    }
}
$config = [
    'id' => 'admission-real-yii-fixture', 'basePath' => getenv('ADMISSION_FIXTURE_BASE'),
    'controllerMap' => ['fixture' => FixtureController::class], 'defaultRoute' => 'fixture/index',
    'bootstrap' => [static function ($app): void { fixtureTrace('bootstrap-before-session:' . session_status()); fixtureTrace('private-key-visible:' . (int) (isset($_SERVER['IDENTITY_DEV_ADMISSION_KEY']) || isset($_ENV['IDENTITY_DEV_ADMISSION_KEY']))); fixtureTrace('exception-args-private:' . (int) filter_var(ini_get('zend.exception_ignore_args'), FILTER_VALIDATE_BOOLEAN)); }],
    'components' => [
        'request' => ['cookieValidationKey' => 'synthetic-fixture-cookie-key', 'enableCsrfValidation' => false],
        'response' => ['format' => \yii\web\Response::FORMAT_RAW, 'on afterSend' => static fn() => fixtureTrace('response-sent')],
        'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite:' . getenv('ADMISSION_FIXTURE_BASE') . '/fixture.db'],
        'session' => ['savePath' => getenv('ADMISSION_FIXTURE_BASE') . '/sessions', 'name' => 'identityFixtureSession'],
        'log' => ['targets' => []],
    ],
];
if ($case === 'custom-yii-handler') {
    class FixtureErrorHandler extends \yii\web\ErrorHandler {}
    $config['components']['errorHandler'] = ['class' => FixtureErrorHandler::class];
}
if (class_exists($runnerClass, false) && $runnerClass::isRunning()) { $class = $runnerClass::applicationClass(); (new $class($config))->run(); }
else (new \yii\web\Application($config))->run();
