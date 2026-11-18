<?php

namespace api\modules\v1\controllers;

use api\modules\v1\components\DeviceSnAuthContext;
use api\modules\v1\models\User;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\File;
use api\modules\v1\services\SubtitleResources;
use api\modules\v1\services\SubtitleFiles;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use OpenApi\Annotations as OA;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\rest\OptionsAction;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

final class SubtitleController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => CompositeAuth::class,
            'authMethods' => [JwtHttpBearerAuth::class], 'except' => ['options'], 'optional' => ['snapshot-file']];
        $behaviors['access'] = ['class' => AccessControl::class,
            'allowActions' => ['prepare', 'index', 'create', 'view', 'update', 'binding', 'set-binding', 'upload-bound', 'snapshot-file', 'options']];
        return $behaviors;
    }

    protected function verbs()
    {
        return ['prepare' => ['POST'], 'index' => ['GET'], 'create' => ['POST'], 'view' => ['GET'], 'update' => ['PUT'],
            'binding' => ['GET'], 'set-binding' => ['PUT'], 'upload-bound' => ['POST'], 'snapshot-file' => ['GET'], 'options' => ['OPTIONS']];
    }

    public function actions()
    {
        return ['options' => ['class' => OptionsAction::class, 'collectionOptions' => ['GET', 'POST', 'PUT', 'OPTIONS']]];
    }

    private function authorized(callable $operation): array
    {
        Yii::$app->response->headers->set('Cache-Control', 'private, no-store');
        $user = Yii::$app->user->identity;
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException();
        }
        if (!SubtitleResources::enabled()
            || (int)$user->status !== 10 || DeviceSnAuthContext::normalize($user->authContext) !== []) {
            throw new ForbiddenHttpException('当前会话无法管理字幕资源。');
        }
        $db = Yii::$app->get('subtitleDb');
        return $db->transaction(function () use ($db, $operation, $user) {
            $id = (int)$user->id;
            $sql = 'SELECT [[status]] FROM {{%user}} WHERE [[id]] = :id';
            if ($db->driverName === 'mysql') {
                $sql .= ' FOR UPDATE';
            }
            if ((int)$db->createCommand($sql, [':id' => $id])->queryScalar() !== 10) {
                throw new ForbiddenHttpException('当前账号无法管理字幕资源。');
            }
            return $operation(new SubtitleResources($db, $id));
        });
    }

    /** @OA\Get(path="/v1/verses/{verseId}/snapshot/subtitles/{fileId}", tags={"Verse"},
     * @OA\Parameter(name="verseId", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="fileId", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Locally stored subtitle JSON"),
     * @OA\Response(response=302, description="Public file URL or a five-minute GET URL for a legacy private COS file",
     *   @OA\Header(header="Location", @OA\Schema(type="string", format="uri"))),
     * @OA\Response(response=404, description="Scene unavailable or file outside its current snapshot")) */
    public function actionSnapshotFile(int $verseId, int $fileId): Response
    {
        $response = Yii::$app->response;
        $response->headers->set('Cache-Control', 'private, no-store');
        if (!SubtitleResources::enabled()) throw new NotFoundHttpException();
        $snapshot = Snapshot::findOne(['verse_id' => $verseId]);
        if (!$snapshot?->verse?->viewable) throw new NotFoundHttpException();

        foreach ($snapshot->toArray([], ['resources'])['resources'] ?? [] as $resource) {
            if (($resource['type'] ?? null) !== 'subtitle' || (int) ($resource['file']['id'] ?? 0) !== $fileId) continue;
            $file = File::findOne($fileId);
            $captured = $resource['file'];
            if (!$file || $file->md5 !== ($captured['md5'] ?? null)
                || !in_array($captured['key'] ?? null, [$file->key, $file->md5 . '.json'], true)) break;
            $owner = (int) $file->user_id;
            $attributes = $file->getAttributes();
            $url = SubtitleFiles::publicUrl($owner, $attributes) ?? SubtitleFiles::signedDownloadUrl($owner, $attributes);
            if ($url !== null) {
                return $response->redirect($url, 302, false);
            }
            $json = SubtitleFiles::read($owner, $attributes);
            return $response->sendContentAsFile($json, $file->md5 . '.json', ['mimeType' => 'application/json', 'inline' => true]);
        }
        throw new NotFoundHttpException();
    }

    /** @OA\Post(path="/v1/subtitles/prepare", security={{"Bearer":{}}},
     * @OA\Response(response=200, description="Validated JSON bytes and the owner's public upload directory")) */
    public function actionPrepare(): array
    {
        return $this->authorized(fn () => [
            'json' => SubtitleResources::json(SubtitleResources::parseSrt(Yii::$app->request->post('srt'))),
            'directory' => SubtitleResources::FILE_PREFIX . (int) Yii::$app->user->id,
        ]);
    }

    /** @OA\Get(path="/v1/subtitles", security={{"Bearer":{}}},
     * @OA\Parameter(name="search", in="query", @OA\Schema(type="string"), description="Substring of the subtitle asset name"),
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     * @OA\Response(response=200, description="Matching own subtitle assets, 20 per page")) */
    public function actionIndex(int $page = 1, string $search = ''): array
    {
        return $this->authorized(fn ($resources) => $resources->listing(max(1, min(100000000, $page)), $search));
    }

    /** @OA\Get(path="/v1/subtitles/{id}", security={{"Bearer":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Metadata and timed cues in milliseconds")) */
    public function actionView(int $id): array
    {
        return $this->authorized(fn ($resources) => $resources->view($id));
    }

    /** @OA\Post(path="/v1/subtitles", security={{"Bearer":{}}},
     * @OA\RequestBody(required=true, @OA\JsonContent(required={"name","srt","uploadedFileId"}, @OA\Property(property="name",type="string"), @OA\Property(property="uploadedFileId",type="integer"), @OA\Property(property="srt",type="string"))),
     * @OA\Response(response=201, description="Created subtitle asset")) */
    public function actionCreate(): array
    {
        $result = $this->save(null);
        Yii::$app->response->statusCode = 201;
        return $result;
    }

    /** @OA\Put(path="/v1/subtitles/{id}", security={{"Bearer":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(required={"fileId","name","srt"}, @OA\Property(property="fileId",type="integer"), @OA\Property(property="name",type="string"), @OA\Property(property="uploadedFileId",type="integer"), @OA\Property(property="srt",type="string"))),
     * @OA\Response(response=200, description="Saved a new version"), @OA\Response(response=409, description="Version conflict")) */
    public function actionUpdate(int $id): array
    {
        return $this->save($id);
    }

    /** @OA\Get(path="/v1/resources/{id}/subtitle", security={{"Bearer":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Subtitle asset reference or null for an owned audio/video")) */
    public function actionBinding(int $id): array
    {
        return $this->authorized(fn ($resources) => $resources->binding($id));
    }

    /** @OA\Put(path="/v1/resources/{id}/subtitle", security={{"Bearer":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(required={"subtitleResourceId","previousSubtitleResourceId"},
     * @OA\Property(property="subtitleResourceId",type="integer",nullable=true),
     * @OA\Property(property="previousSubtitleResourceId",type="integer",nullable=true))),
     * @OA\Response(response=200, description="Updated binding"), @OA\Response(response=409, description="Binding changed")) */
    public function actionSetBinding(int $id): array
    {
        return $this->authorized(fn ($resources) => $resources->setBinding($id, Yii::$app->request->bodyParams));
    }

    /** @OA\Post(path="/v1/resources/{id}/subtitle", security={{"Bearer":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(required={"name","srt","previousSubtitleResourceId","uploadedFileId"},
     * @OA\Property(property="name",type="string"), @OA\Property(property="uploadedFileId",type="integer"), @OA\Property(property="srt",type="string"),
     * @OA\Property(property="previousSubtitleResourceId",type="integer",nullable=true))),
     * @OA\Response(response=201, description="Created and bound a subtitle asset"),
     * @OA\Response(response=409, description="Binding changed"), @OA\Response(response=422, description="Invalid subtitle")) */
    public function actionUploadBound(int $id): array
    {
        $result = $this->authorized(fn ($resources) => $resources->uploadBound($id, Yii::$app->request->bodyParams));
        Yii::$app->response->statusCode = 201;
        return $result;
    }

    private function save(?int $id): array
    {
        return $this->authorized(fn ($resources) => $resources->save($id, Yii::$app->request->bodyParams));
    }
}
