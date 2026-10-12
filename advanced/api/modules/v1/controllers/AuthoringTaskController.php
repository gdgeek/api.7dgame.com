<?php

namespace api\modules\v1\controllers;

use api\modules\v1\services\AuthoringTaskStore;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use OpenApi\Annotations as OA;
use common\components\security\CorsOriginPolicy;
use yii\filters\auth\CompositeAuth;
/** Caller-owned durable orchestration; object writes keep their original authorization. */
class AuthoringTaskController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $b = parent::behaviors();
        unset($b['authenticator']);
        $b['corsFilter'] = ['class' => \yii\filters\Cors::class, 'cors' => CorsOriginPolicy::yiiConfiguration()];
        $b['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [['class' => JwtHttpBearerAuth::class, 'throwException' => false]],
            'except' => ['options'],
        ];
        $b['access'] = ['class' => AccessControl::class, 'except' => ['options']];
        $b['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => ['index' => ['GET'], 'view' => ['GET'], 'create' => ['POST'], 'claim' => ['POST'], 'checkpoint' => ['PUT']],
        ];
        return $b;
    }
    public function actions(): array
    {
        return ['options' => ['class' => \yii\rest\OptionsAction::class]];
    }
    /** Keep arbitrary tool inputs/results as JSON values, including nested empty objects. */
    private function body(): array
    {
        $body = Yii::$app->request->bodyParams;
        $raw = Yii::$app->request->getRawBody();
        if ($raw === '') {
            return $body;
        }
        try {
            $json = json_decode($raw, false, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \yii\web\BadRequestHttpException('Invalid task JSON');
        }
        foreach ($json->steps ?? [] as $i => $step) {
            if (isset($body['steps'][$i]) && $step instanceof \stdClass && property_exists($step, 'input')) {
                $body['steps'][$i]['input'] = $step->input;
            }
        }
        foreach ($json->progress->states ?? [] as $i => $step) {
            if (isset($body['progress']['states'][$i]) && $step instanceof \stdClass && property_exists($step, 'result')) {
                $body['progress']['states'][$i]['result'] = $step->result;
            }
        }
        return $body;
    }
    /** @OA\Get(path="/v1/authoring-tasks", summary="List own durable tasks", tags={"WebMCP"}, security={{"Bearer": {}}}, @OA\Response(response=200, description="Paginated task summaries")) */
    public function actionIndex($offset = 0): array
    {
        return AuthoringTaskStore::listing((int) $offset);
    }
    /**
     * @OA\Get(path="/v1/authoring-tasks/{id}", summary="Read own saved task", tags={"WebMCP"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Task progress and revision"),
     * @OA\Response(response=404, description="Not owned or missing"),
     * @OA\Response(response=409, description="Stale revision or active/expired lease"))
     */
    public function actionView($id): array
    {
        return AuthoringTaskStore::get($id);
    }
    /** @OA\Post(path="/v1/authoring-tasks", summary="Persist an immutable authoring plan", tags={"WebMCP"}, security={{"Bearer": {}}}, @OA\RequestBody(required=true, @OA\JsonContent(type="object", required={"taskId","name","steps"}, @OA\Property(property="taskId", type="string", format="uuid"), @OA\Property(property="name", type="string"), @OA\Property(property="steps", type="array", @OA\Items(type="object")))), @OA\Response(response=200, description="Task with progress revision"), @OA\Response(response=409, description="Task ID conflict or quota")) */
    public function actionCreate(): array
    {
        return AuthoringTaskStore::create($this->body());
    }
    /**
     * @OA\Post(path="/v1/authoring-tasks/{id}/claim", summary="Claim current progress revision", tags={"WebMCP"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Task progress and revision"),
     * @OA\Response(response=404, description="Not owned or missing"),
     * @OA\Response(response=409, description="Stale revision or active/expired lease"))
     */
    public function actionClaim($id): array
    {
        return AuthoringTaskStore::change($id, $this->body(), 'claim');
    }
    /**
     * @OA\Put(path="/v1/authoring-tasks/{id}/checkpoint", summary="Save evidence under the original lease and revision", tags={"WebMCP"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Task progress and revision"),
     * @OA\Response(response=404, description="Not owned or missing"),
     * @OA\Response(response=409, description="Stale revision or active/expired lease"))
     */
    public function actionCheckpoint($id): array
    {
        return AuthoringTaskStore::change($id, $this->body(), 'checkpoint');
    }
}
