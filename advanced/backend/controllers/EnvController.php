<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * 环境变量检测控制器
 */
class EnvController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 检测所有环境变量
     */
    public function actionIndex()
    {
        $requiredVars = $this->getRequiredVariables();
        $results = [];
        $missingCount = 0;
        $emptyCount = 0;

        foreach ($requiredVars as $group => $vars) {
            $results[$group] = [];
            foreach ($vars as $var => $info) {
                $value = getenv($var);
                $status = 'ok';
                
                if ($value === false) {
                    $status = 'missing';
                    if ($info['required']) {
                        $emptyCount++;
                    } else {
                        $missingCount++;
                    }
                } elseif ($value === '' && $info['required']) {
                    $status = 'empty';
                    $emptyCount++;
                }

                $results[$group][$var] = [
                    'value' => $value !== false ? $this->maskSensitive($var, $value) : null,
                    'status' => $status,
                    'description' => $info['description'],
                    'required' => $info['required'],
                    'default' => $info['default'] ?? null,
                ];
            }
        }

        return $this->render('index', [
            'results' => $results,
            'missingCount' => $missingCount,
            'emptyCount' => $emptyCount,
        ]);
    }

    /**
     * 获取所有需要检测的环境变量
     */
    private function getRequiredVariables()
    {
        return [
            '数据库配置' => [
                'MYSQL_HOST' => ['description' => '数据库主机', 'required' => true],
                'MYSQL_PORT' => ['description' => '数据库端口', 'required' => false, 'default' => '3306'],
                'MYSQL_DB' => ['description' => '数据库名称', 'required' => true],
                'MYSQL_USERNAME' => ['description' => '数据库用户名', 'required' => true],
                'MYSQL_PASSWORD' => ['description' => '数据库密码', 'required' => true],
            ],
            'JWT 配置' => [
                'JWT_KEY' => ['description' => 'JWT 私钥文件路径', 'required' => true],
                'JWT_PUBLIC_KEY' => ['description' => 'JWT 公钥文件路径', 'required' => false],
                'JWT_SECRET' => ['description' => 'JWT 密钥 (HS256)', 'required' => false],
                'JWT_ALGORITHM' => ['description' => 'JWT 算法', 'required' => false, 'default' => 'ES256'],
                'JWT_ISSUER' => ['description' => 'JWT 签发者', 'required' => false, 'default' => 'yii2-app'],
                'JWT_ACCESS_TOKEN_EXPIRY' => ['description' => '访问令牌过期时间(秒)', 'required' => false, 'default' => '3600'],
                'JWT_REFRESH_TOKEN_EXPIRY' => ['description' => '刷新令牌过期时间(秒)', 'required' => false, 'default' => '604800'],
                'JWT_KEY_PASSPHRASE' => ['description' => 'JWT 密钥密码', 'required' => false],
                'JWT_PREVIOUS_SECRET' => ['description' => '旧 JWT 密钥 (轮换用)', 'required' => false],
                'JWT_PREVIOUS_KEY' => ['description' => '旧 JWT 私钥路径 (轮换用)', 'required' => false],
                'JWT_KEY_ROTATION_GRACE_PERIOD' => ['description' => '密钥轮换宽限期(秒)', 'required' => false, 'default' => '86400'],
            ],
            '腾讯云配置' => [
                'SECRET_ID' => ['description' => '腾讯云 SecretId', 'required' => true],
                'SECRET_KEY' => ['description' => '腾讯云 SecretKey', 'required' => true],
            ],
            'COS 存储配置' => [
                'COS_BUCKETS_PUBLIC_BUCKET' => ['description' => '公开存储桶名称', 'required' => true],
                'COS_BUCKETS_PUBLIC_REGION' => ['description' => '公开存储桶区域', 'required' => true],
                'COS_BUCKETS_PRIVATE_BUCKET' => ['description' => '私有存储桶名称', 'required' => true],
                'COS_BUCKETS_PRIVATE_REGION' => ['description' => '私有存储桶区域', 'required' => true],
            ],
            '邮件配置' => [
                'MAILER_USERNAME' => ['description' => '邮箱账号', 'required' => true],
                'MAILER_PASSWORD' => ['description' => '邮箱授权码', 'required' => true],
            ],
            'Redis 配置' => [
                'REDIS_HOST' => ['description' => 'Redis 主机', 'required' => true, 'default' => '127.0.0.1'],
                'REDIS_PORT' => ['description' => 'Redis 端口', 'required' => true, 'default' => '6379'],
                'REDIS_DB' => ['description' => 'Redis 数据库编号', 'required' => false, 'default' => '0'],
                'REDIS_PASSWORD' => ['description' => 'Redis 密码', 'required' => false],
                'REDIS_SECURITY_DB' => ['description' => 'Redis 安全数据库编号', 'required' => false, 'default' => '1'],
                'REDIS_TIMEOUT' => ['description' => 'Redis 超时时间', 'required' => false, 'default' => '2.5'],
                'REDIS_KEY_PREFIX' => ['description' => 'Redis 键前缀', 'required' => false, 'default' => 'security:'],
                'REDIS_ENABLE_RATE_LIMIT' => ['description' => '启用速率限制', 'required' => false, 'default' => 'true'],
                'REDIS_ENABLE_TOKEN_REVOCATION' => ['description' => '启用令牌撤销', 'required' => false, 'default' => 'true'],
            ],
            'Swagger 文档' => [
                'SWAGGER_USERNAME' => ['description' => 'Swagger 用户名', 'required' => false, 'default' => 'swagger_admin'],
                'SWAGGER_PASSWORD' => ['description' => 'Swagger 密码', 'required' => false],
                'SWAGGER_ENABLED' => ['description' => 'Swagger 是否启用', 'required' => false, 'default' => 'true'],
            ],
            '应用配置' => [
                'APP_ENV' => ['description' => '应用环境', 'required' => false, 'default' => 'development'],
                'APP_DEBUG' => ['description' => '调试模式', 'required' => false, 'default' => 'false'],
                'APP_TITLE' => ['description' => '应用标题', 'required' => false],
                'APP_VERSION' => ['description' => '应用版本', 'required' => false],
            ],
            '应用信息 (MRPP)' => [
                'MRPP_TITLE' => ['description' => '应用标题', 'required' => false],
                'MRPP_SUB_TITLE' => ['description' => '应用副标题', 'required' => false],
                'MRPP_COMPANY' => ['description' => '公司名称', 'required' => false],
                'MRPP_COMPANY_URL' => ['description' => '公司网址', 'required' => false],
                'MRPP_AKA' => ['description' => '应用简称', 'required' => false],
                'MRPP_LOCAL' => ['description' => '本地路径', 'required' => false],
                'MRPP_IP' => ['description' => 'IP 地址', 'required' => false],
                'MRPP_DISK' => ['description' => '磁盘路径', 'required' => false],
                'MRPP_API' => ['description' => 'API 地址', 'required' => false],
                'MRPP_PUB' => ['description' => '公开地址', 'required' => false],
            ],
            'Apple 登录' => [
                'APPLE_TEAM_ID' => ['description' => 'Apple Team ID', 'required' => false],
                'APPLE_AUTH_KEY' => ['description' => 'Apple Auth Key 路径', 'required' => false],
            ],
            '速率限制' => [
                'RATE_LIMIT_ENABLED' => ['description' => '启用速率限制', 'required' => false, 'default' => 'true'],
                'RATE_LIMIT_IP_PER_MINUTE' => ['description' => '每分钟每IP请求数', 'required' => false, 'default' => '100'],
                'RATE_LIMIT_USER_PER_HOUR' => ['description' => '每小时每用户请求数', 'required' => false, 'default' => '1000'],
                'RATE_LIMIT_LOGIN_ATTEMPTS' => ['description' => '登录尝试次数', 'required' => false, 'default' => '5'],
                'RATE_LIMIT_LOGIN_WINDOW' => ['description' => '登录窗口时间(分钟)', 'required' => false, 'default' => '15'],
            ],
            '密码策略' => [
                'PASSWORD_MIN_LENGTH' => ['description' => '最小密码长度', 'required' => false, 'default' => '12'],
                'PASSWORD_REQUIRE_UPPERCASE' => ['description' => '要求大写字母', 'required' => false, 'default' => 'true'],
                'PASSWORD_REQUIRE_LOWERCASE' => ['description' => '要求小写字母', 'required' => false, 'default' => 'true'],
                'PASSWORD_REQUIRE_DIGIT' => ['description' => '要求数字', 'required' => false, 'default' => 'true'],
                'PASSWORD_REQUIRE_SPECIAL' => ['description' => '要求特殊字符', 'required' => false, 'default' => 'true'],
                'PASSWORD_HISTORY_COUNT' => ['description' => '密码历史记录数', 'required' => false, 'default' => '5'],
                'PASSWORD_ADMIN_EXPIRY_DAYS' => ['description' => '管理员密码过期天数', 'required' => false, 'default' => '90'],
                'PASSWORD_BCRYPT_COST' => ['description' => 'Bcrypt 成本因子', 'required' => false, 'default' => '12'],
                'WEAK_PASSWORD_LIST_PATH' => ['description' => '弱密码列表路径', 'required' => false],
            ],
            '账户锁定' => [
                'ACCOUNT_LOCKOUT_THRESHOLD' => ['description' => '锁定阈值(失败次数)', 'required' => false, 'default' => '5'],
                'ACCOUNT_LOCKOUT_DURATION' => ['description' => '锁定时长(分钟)', 'required' => false, 'default' => '30'],
                'ACCOUNT_LOCKOUT_WINDOW' => ['description' => '检测窗口(分钟)', 'required' => false, 'default' => '15'],
            ],
            '会话配置' => [
                'SESSION_TIMEOUT_MINUTES' => ['description' => '会话超时(分钟)', 'required' => false, 'default' => '30'],
                'SESSION_REGENERATE_ON_AUTH' => ['description' => '认证时重新生成会话', 'required' => false, 'default' => 'true'],
            ],
            '文件上传' => [
                'UPLOAD_MAX_FILE_SIZE' => ['description' => '最大文件大小(字节)', 'required' => false, 'default' => '10485760'],
                'UPLOAD_PATH' => ['description' => '上传路径', 'required' => false, 'default' => '/var/uploads'],
            ],
            'CORS 配置' => [
                'CORS_ALLOWED_ORIGINS' => ['description' => '允许的来源(逗号分隔)', 'required' => false],
                'CORS_ALLOW_CREDENTIALS' => ['description' => '允许凭证', 'required' => false, 'default' => 'true'],
                'CORS_MAX_AGE' => ['description' => '预检缓存时间(秒)', 'required' => false, 'default' => '3600'],
            ],
            '审计日志' => [
                'AUDIT_ENABLED' => ['description' => '启用审计日志', 'required' => false, 'default' => 'true'],
                'AUDIT_RETENTION_DAYS' => ['description' => '日志保留天数', 'required' => false, 'default' => '90'],
                'AUDIT_LOG_PATH' => ['description' => '日志路径', 'required' => false, 'default' => '@runtime/logs/security'],
            ],
            '错误处理' => [
                'SHOW_DETAILED_ERRORS' => ['description' => '显示详细错误', 'required' => false, 'default' => 'false'],
                'ALERT_ON_CRITICAL_ERRORS' => ['description' => '严重错误时告警', 'required' => false, 'default' => 'true'],
                'ALERT_EMAILS' => ['description' => '告警邮箱', 'required' => false],
            ],
        ];
    }

    /**
     * 对敏感值进行脱敏处理
     */
    private function maskSensitive($var, $value)
    {
        $sensitiveKeys = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN'];
        
        foreach ($sensitiveKeys as $key) {
            if (stripos($var, $key) !== false && !empty($value)) {
                $len = strlen($value);
                if ($len <= 4) {
                    return '****';
                }
                return substr($value, 0, 2) . str_repeat('*', $len - 4) . substr($value, -2);
            }
        }
        
        return $value;
    }
}
