#!/bin/bash
# 批量为控制器添加 OpenAPI Tag 注解

# 定义控制器和对应的标签
declare -A controllers=(
    ["modules/v1/controllers/TencentCloudController.php"]="TencentCloud|腾讯云服务相关接口"
    ["modules/v1/controllers/PhototypeController.php"]="Phototype|照片类型管理接口"
    ["modules/v1/controllers/WechatController.php"]="Wechat|微信相关接口"
    ["modules/v1/controllers/AuthController.php"]="Auth|认证授权接口"
    ["modules/v1/controllers/UploadController.php"]="Upload|文件上传接口"
    ["modules/v1/controllers/ToolsController.php"]="Tools|工具类接口"
    ["modules/v1/controllers/VerseTagsController.php"]="VerseTags|Verse标签管理接口"
    ["modules/v1/controllers/SystemController.php"]="System|系统管理接口"
    ["modules/v1/controllers/MetaController.php"]="Meta|Meta元数据管理接口"
    ["modules/v1/controllers/PrefabController.php"]="Prefab|预制件管理接口"
    ["modules/v1/controllers/FileController.php"]="File|文件管理接口"
    ["modules/v1/controllers/ResourceController.php"]="Resource|资源管理接口"
    ["modules/v1/controllers/TagsController.php"]="Tags|标签管理接口"
    ["modules/v1/controllers/TokenController.php"]="Token|Token管理接口"
    ["modules/v1/controllers/GroupVerseController.php"]="GroupVerse|群组Verse管理接口"
    ["modules/v1/controllers/EduSchoolController.php"]="EduSchool|教育-学校管理接口"
    ["modules/v1/controllers/EduClassController.php"]="EduClass|教育-班级管理接口"
    ["modules/v1/controllers/GroupController.php"]="Group|群组管理接口"
    ["modules/v1/controllers/EduStudentController.php"]="EduStudent|教育-学生管理接口"
    ["modules/v1/controllers/EduTeacherController.php"]="EduTeacher|教育-教师管理接口"
    ["modules/v1/controllers/DomainController.php"]="Domain|域名管理接口"
    ["modules/v1/controllers/VerseController.php"]="Verse|Verse管理接口"
    ["modules/v1/controllers/PersonController.php"]="Person|人员管理接口"
    ["modules/v1/controllers/SiteController.php"]="V1Site|V1站点相关接口"
)

echo "批量添加 OpenAPI Tag 注解"
echo "========================"
echo ""

for file in "${!controllers[@]}"; do
    IFS='|' read -r tag description <<< "${controllers[$file]}"
    
    if [ -f "$file" ]; then
        echo "处理: $file"
        echo "  标签: $tag"
        echo "  描述: $description"
        
        # 检查是否已经有 OpenApi\Annotations 导入
        if ! grep -q "use OpenApi\\\\Annotations as OA;" "$file"; then
            echo "  -> 添加 OpenApi\Annotations 导入"
        fi
        
        # 检查是否已经有 @OA\Tag 注解
        if ! grep -q "@OA\\\\Tag" "$file"; then
            echo "  -> 需要添加 @OA\Tag 注解"
        else
            echo "  -> 已有 @OA\Tag 注解，跳过"
        fi
        
        echo ""
    else
        echo "文件不存在: $file"
        echo ""
    fi
done

echo "提示: 这是一个检查脚本。实际添加注解需要手动或使用 Kiro 完成。"
