<?php

// This fixture is served only by the test's loopback PHP server. It is not an API entry point.
if (PHP_SAPI !== 'cli-server' || ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1') {
    http_response_code(404);
    exit;
}

define('YII_DEBUG', false);
define('YII_ENV', 'test');
$advanced = dirname(__DIR__, 3);
require $advanced . '/vendor/autoload.php';
require $advanced . '/vendor/yiisoft/yii2/Yii.php';
Yii::setAlias('@api', $advanced . '/api');
Yii::setAlias('@common', $advanced . '/common');
require __DIR__ . '/WebMcpHttpFixture.php';

use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use tests\integration\fixtures\WebMcpHttpFixture;
use tests\integration\fixtures\WebMcpHttpIdentity;

try {
    new yii\console\Application([
        'id' => 'webmcp-http-fixture', 'basePath' => $advanced,
        'components' => [
            'db' => WebMcpHttpFixture::databaseConfig(),
            'cache' => ['class' => yii\caching\ArrayCache::class],
            'request' => ['class' => yii\web\Request::class, 'cookieValidationKey' => 'test-only',
                'parsers' => ['application/json' => yii\web\JsonParser::class]],
            'user' => ['class' => yii\web\User::class, 'identityClass' => WebMcpHttpIdentity::class,
                'enableSession' => false, 'loginUrl' => null],
        ],
    ]);
    WebMcpHttpFixture::assertTestDatabase(Yii::$app->db);
    $actor = Yii::$app->request->headers->get('X-WebMCP-Test-Actor', '7');
    if (!in_array($actor, ['7', '8'], true)) throw new yii\web\UnauthorizedHttpException();
    Yii::$app->user->switchIdentity(new WebMcpHttpIdentity((int) $actor));
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = Yii::$app->request->method;
    $controller = new VerseController('verse', Yii::$app);
    if ($method === 'GET' && $path === '/ready') {
        Yii::$app->db->createCommand('SELECT 1')->queryScalar();
        $result = ['ready' => true];
    } elseif ($method === 'PUT' && $path === '/v1/verses/1') {
        $body = Yii::$app->request->bodyParams;
        // Exercise the production transaction/guard/receipt service with a minimal mutation.
        // The separate browser matrix covers full editor serialization and controller writes.
        $result = ReliableWrite::run('verse', 1, 'save', $body,
            fn(Verse $model) => $controller->checkAccess('update', $model),
            function () use ($body): array {
                Yii::$app->db->createCommand()->update('verse', ['name' => $body['name']], ['id' => 1])->execute();
                Yii::$app->db->createCommand('UPDATE webmcp_test_write_counter SET commits = commits + 1 WHERE id = 1')->execute();
                return ['id' => 1];
            });
    } elseif ($method === 'GET' && preg_match('~\A/v1/verses/1/operations/([0-9a-f-]+)\z~iD', $path, $match)) {
        $result = $controller->actionOperation(1, $match[1]);
    } else {
        throw new yii\web\NotFoundHttpException();
    }
    $status = 200;
} catch (yii\web\HttpException $error) {
    $status = $error->statusCode;
    $result = ['error' => get_class($error)];
} catch (Throwable $error) {
    $status = 500;
    $result = ['error' => get_class($error)];
}
$json = json_encode($result, JSON_THROW_ON_ERROR);
http_response_code($status);
header('Content-Type: application/json');
header('Content-Length: ' . strlen($json));
header('Connection: close');
echo $json;
