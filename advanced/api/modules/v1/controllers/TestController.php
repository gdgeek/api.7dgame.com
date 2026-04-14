<?php
namespace api\modules\v1\controllers;

use api\modules\v1\RefreshToken;
use Yii;
use yii\rest\ActiveController;

class TestController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Token';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return $behaviors;
    }

    /**
     * 数据库连接调试页面
     * GET /v1/test/db-debug
     */
    public function actionDbDebug()
    {
        $result = [
            'timestamp' => date('Y-m-d H:i:s'),
            'connections' => [],
        ];

        // 测试主数据库 (db)
        $result['connections']['db'] = $this->testConnection('db', 'bujiaban');

        // 测试 Redis
        $result['connections']['redis'] = $this->testRedis();

        return $result;
    }

    /**
     * 测试数据库连接
     */
    private function testConnection(string $componentName, string $expectedDb): array
    {
        $info = [
            'component' => $componentName,
            'status' => 'unknown',
            'dsn' => null,
            'database' => null,
            'tables' => [],
            'error' => null,
        ];

        try {
            $component = Yii::$app->get($componentName, false);
            if (!$component) {
                $info['status'] = 'not_configured';
                $info['error'] = "Component '{$componentName}' not found";
                return $info;
            }

            $info['dsn'] = $component->dsn;

            // 尝试打开连接
            $component->open();
            $info['status'] = 'connected';

            // 获取当前数据库名
            $dbName = $component->createCommand('SELECT DATABASE()')->queryScalar();
            $info['database'] = $dbName;

            // 获取表列表
            $tables = $component->createCommand('SHOW TABLES')->queryColumn();
            $info['tables'] = $tables;
            $info['table_count'] = count($tables);

        } catch (\Exception $e) {
            $info['status'] = 'error';
            $info['error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * 测试 Redis 连接
     */
    private function testRedis(): array
    {
        $info = [
            'component' => 'redis',
            'status' => 'unknown',
            'host' => null,
            'port' => null,
            'database' => null,
            'error' => null,
        ];

        try {
            $redis = Yii::$app->get('redis', false);
            if (!$redis) {
                $info['status'] = 'not_configured';
                $info['error'] = "Component 'redis' not found";
                return $info;
            }

            $info['host'] = $redis->hostname;
            $info['port'] = $redis->port;
            $info['database'] = $redis->database;

            // 尝试 PING
            $pong = $redis->ping();
            $info['status'] = ($pong === 'PONG' || $pong === true) ? 'connected' : 'error';

        } catch (\Exception $e) {
            $info['status'] = 'error';
            $info['error'] = $e->getMessage();
        }

        return $info;
    }

    public function actionFile()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.bilibili.com/x/space/wbi/arc/search?mid=20959246');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        return $output;
    }

    public function actionTest()
    {
        $all = RefreshToken::find()->all();
        $one = RefreshToken::find()->where(['key' => 'KQ5N52i3OAq2jOAL3I0yaMAMCg91PiCb'])->one();

        $one->save();
        return $one;
    }
}
