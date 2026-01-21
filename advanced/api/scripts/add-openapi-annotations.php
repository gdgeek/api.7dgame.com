<?php
/**
 * 批量为控制器添加 OpenAPI 注解的辅助脚本
 * 
 * 使用方法：
 * php scripts/add-openapi-annotations.php
 */

// 需要添加注解的控制器列表（基于 files/api/config/main.php 的路由配置）
$controllers = [
    // 格式: ['path' => '文件路径', 'tag' => '标签名', 'description' => '描述']
    [
        'path' => 'modules/v1/controllers/TencentCloudController.php',
        'tag' => 'TencentCloud',
        'description' => '腾讯云服务相关接口'
    ],
    [
        'path' => 'modules/v1/controllers/PhototypeController.php',
        'tag' => 'Phototype',
        'description' => '照片类型管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/WechatController.php',
        'tag' => 'Wechat',
        'description' => '微信相关接口'
    ],
    [
        'path' => 'modules/v1/controllers/AuthController.php',
        'tag' => 'Auth',
        'description' => '认证授权接口'
    ],
    [
        'path' => 'modules/v1/controllers/UploadController.php',
        'tag' => 'Upload',
        'description' => '文件上传接口'
    ],
    [
        'path' => 'modules/v1/controllers/ToolsController.php',
        'tag' => 'Tools',
        'description' => '工具类接口'
    ],
    [
        'path' => 'modules/v1/controllers/VerseTagsController.php',
        'tag' => 'VerseTags',
        'description' => 'Verse 标签管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/SystemController.php',
        'tag' => 'System',
        'description' => '系统管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/MetaController.php',
        'tag' => 'Meta',
        'description' => 'Meta 元数据管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/PrefabController.php',
        'tag' => 'Prefab',
        'description' => '预制件管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/FileController.php',
        'tag' => 'File',
        'description' => '文件管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/ResourceController.php',
        'tag' => 'Resource',
        'description' => '资源管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/TagsController.php',
        'tag' => 'Tags',
        'description' => '标签管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/TokenController.php',
        'tag' => 'Token',
        'description' => 'Token 管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/GroupVerseController.php',
        'tag' => 'GroupVerse',
        'description' => '群组 Verse 管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/EduSchoolController.php',
        'tag' => 'EduSchool',
        'description' => '教育-学校管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/EduClassController.php',
        'tag' => 'EduClass',
        'description' => '教育-班级管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/GroupController.php',
        'tag' => 'Group',
        'description' => '群组管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/EduStudentController.php',
        'tag' => 'EduStudent',
        'description' => '教育-学生管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/EduTeacherController.php',
        'tag' => 'EduTeacher',
        'description' => '教育-教师管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/DomainController.php',
        'tag' => 'Domain',
        'description' => '域名管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/VerseController.php',
        'tag' => 'Verse',
        'description' => 'Verse 管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/PersonController.php',
        'tag' => 'Person',
        'description' => '人员管理接口'
    ],
    [
        'path' => 'modules/v1/controllers/SiteController.php',
        'tag' => 'V1Site',
        'description' => 'V1 站点相关接口'
    ],
];

echo "OpenAPI 注解添加指南\n";
echo "====================\n\n";
echo "需要为以下 " . count($controllers) . " 个控制器添加 OpenAPI 注解：\n\n";

foreach ($controllers as $index => $controller) {
    echo ($index + 1) . ". {$controller['tag']}: {$controller['description']}\n";
    echo "   文件: {$controller['path']}\n\n";
}

echo "\n建议的注解模板：\n";
echo "==================\n\n";
echo "在控制器类注释中添加：\n\n";
echo "use OpenApi\Annotations as OA;\n\n";
echo "/**\n";
echo " * @OA\Tag(\n";
echo " *     name=\"TagName\",\n";
echo " *     description=\"描述\"\n";
echo " * )\n";
echo " */\n";
echo "class YourController extends Controller\n";
echo "{\n";
echo "    // ...\n";
echo "}\n\n";

echo "在方法注释中添加：\n\n";
echo "/**\n";
echo " * @OA\Get(\n";
echo " *     path=\"/v1/your-endpoint\",\n";
echo " *     summary=\"端点描述\",\n";
echo " *     tags={\"TagName\"},\n";
echo " *     security={{\"Bearer\": {}}},\n";
echo " *     @OA\Response(response=200, description=\"成功\"),\n";
echo " *     @OA\Response(response=401, description=\"未授权\")\n";
echo " * )\n";
echo " */\n";
echo "public function actionYourAction()\n";
echo "{\n";
echo "    // ...\n";
echo "}\n";
