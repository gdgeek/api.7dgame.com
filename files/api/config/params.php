<?php
return [
    'adminEmail' => 'admin@example.com',
    
    // Swagger API 文档访问凭据（从环境变量读取）
    'swagger' => [
        'username' => getenv('SWAGGER_USERNAME') ?: 'swagger_admin',
        'password' => getenv('SWAGGER_PASSWORD') ?: 'YourStrongP@ssw0rd!',
        'enabled' => getenv('SWAGGER_ENABLED') !== 'false', // 生产环境可禁用
    ],
];
