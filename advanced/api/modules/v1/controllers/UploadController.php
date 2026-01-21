<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Upload",
 *     description="文件上传接口"
 * )
 */
class UploadController extends \yii\rest\Controller

{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

      
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        // re-add authentication filter

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }
    public function actionCreation()
    {
        $creation = new UserCreation();
        return $creation;
    }

    /**
     * @OA\Post(
     *     path="/v1/upload/file",
     *     summary="文件上传",
     *     description="分片上传文件到存储系统",
     *     tags={"Upload"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file", "filename", "size", "upload_size", "md5"},
     *                 @OA\Property(property="file", type="string", format="binary", description="上传的文件"),
     *                 @OA\Property(property="filename", type="string", description="文件名", example="document.pdf"),
     *                 @OA\Property(property="size", type="integer", description="文件总大小（字节）", example=1048576),
     *                 @OA\Property(property="upload_size", type="integer", description="已上传大小（字节）", example=524288),
     *                 @OA\Property(property="md5", type="string", description="文件 MD5 校验值", example="5d41402abc4b2a76b9719d911017c592"),
     *                 @OA\Property(property="skip", type="integer", description="跳过字节数（续传）", example=0),
     *                 @OA\Property(property="directory", type="string", description="目标目录", example="uploads/2024"),
     *                 @OA\Property(property="bucket", type="string", description="存储桶名称", example="default")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="上传成功或进行中",
     *         @OA\JsonContent(
     *             @OA\Property(property="over", type="boolean", description="是否上传完成", example=true),
     *             @OA\Property(property="target", type="string", description="目标路径（完成时）"),
     *             @OA\Property(property="data", type="object", description="请求数据")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误 - 文件缺失或校验失败"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionFile()
    {

        $storage = new \common\components\Storage();
        if (!$storage->init()) {
            throw new Exception('文件系统初始化失败', 400);
        }
        $data = \Yii::$app->request->post();

        $skip = ArrayHelper::getValue($data, 'skip'); //['skip'];
        $filename = ArrayHelper::getValue($data, 'filename'); //['skip'];
        $size = ArrayHelper::getValue($data, 'size'); //['skip'];
        $upload_size = ArrayHelper::getValue($data, 'upload_size'); //['skip'];
        $md5 = ArrayHelper::getValue($data, 'md5'); //['skip'];
        $directory = ArrayHelper::getValue($data, 'directory'); //['skip'];
        $bucket = ArrayHelper::getValue($data, 'bucket'); //['skip'];

        $file = UploadedFile::getInstanceByName('file');

        if ($file == null) {
            throw new Exception('没有上传文件', 400);
        }

        $path = $storage->tempDirector . $filename;
        if ($skip == 0) {
            $file->saveAs($path);
        } else {
            file_put_contents($path, file_get_contents($file->tempName), FILE_APPEND);
        }

        if ($size <= $upload_size) {
            if ($md5 == md5_file($path)) {
                rename($path, $storage->targetDirector($bucket, $directory) . $filename);
                return json_encode(['target' => $storage->targetDirector($bucket, $directory), 'over' => true, 'data' => $data]);
            }
        }

        return json_encode(['over' => false, 'data' => $data]);

    }

}
