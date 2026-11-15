<?php
namespace api\modules\v1\controllers;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use api\modules\v1\models\data\VerseCodeTool;

use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;
class SnapshotController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Snapshot';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        return $behaviors;
    }


    /**
     * @OA\Post(path="/v1/snapshot/take-photo", summary="创建或覆盖有编辑权限的场景快照", tags={"Verse"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="verse_id", in="query", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Response(response=200, description="已保存的场景快照"), @OA\Response(response=400, description="场景 ID 无效或内容为空"),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="没有场景编辑权限"),
     * @OA\Response(response=404, description="场景不存在"))
     */
    public function actionTakePhoto($verse_id)
    {
        if ((!is_int($verse_id) && (!is_string($verse_id) || !preg_match('/^[1-9][0-9]*$/D', $verse_id)))
            || filter_var($verse_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new BadRequestHttpException('verse_id must be a positive integer');
        }
        $verse_id = (int) $verse_id;
        $verse = Verse::findOne($verse_id);
        if ($verse === null) {
            throw new NotFoundHttpException('Verse not found');
        }
        if (!$verse->editable) {
            throw new ForbiddenHttpException('You are not allowed to publish this scene');
        }
        $snapshot = Snapshot::CreateById($verse_id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot->toArray([], Snapshot::TAKE_PHOTO_EXTRA_FIELDS);
    }
}
