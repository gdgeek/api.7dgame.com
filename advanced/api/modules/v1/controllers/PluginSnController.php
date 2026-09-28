<?php

namespace api\modules\v1\controllers;

use api\modules\v1\services\DeviceSnService;
use api\modules\v1\services\PluginAccessService;
use api\modules\v1\models\User;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use OpenApi\Annotations as OA;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @OA\Tag(name="Device SN", description="Configured SN distribution and device authorization")
 */
class PluginSnController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [JwtHttpBearerAuth::class],
            'except' => ['options'],
        ];
        // These authenticated actions enforce the live plugin configuration in
        // their own guard. Legacy root-only route grants must not override it.
        $behaviors['access'] = ['class' => AccessControl::class, 'allowActions' => [
            'options', 'access', 'index', 'view', 'accounts', 'generate', 'update', 'reveal', 'export',
        ]];
        return $behaviors;
    }

    public function actions()
    {
        return ['options' => ['class' => \yii\rest\OptionsAction::class]];
    }

    protected function verbs()
    {
        return ['access' => ['GET', 'HEAD'], 'index' => ['GET'], 'view' => ['GET'], 'accounts' => ['GET'], 'generate' => ['POST'],
            'update' => ['PATCH'], 'reveal' => ['POST'], 'export' => ['POST'], 'options' => ['OPTIONS']];
    }

    protected function service(): DeviceSnService
    {
        return new DeviceSnService();
    }

    protected function accessService(): PluginAccessService
    {
        return new PluginAccessService();
    }

    protected function authenticatedUser(): User
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store');
        Yii::$app->response->headers->set('Pragma', 'no-cache');
        $user = Yii::$app->user->identity;
        if (!$user instanceof User || (int)$user->status !== 10) {
            throw new UnauthorizedHttpException('Authentication is required.');
        }
        return $user;
    }

    protected function requireManagementAccess(): int
    {
        $user = $this->authenticatedUser();
        if (!$this->accessService()->resolve('sn-management', $user)['allowed']) {
            throw new ForbiddenHttpException('SN management access is not allowed.');
        }
        return (int)$user->id;
    }

    /** @OA\Get(path="/v1/plugin-sn/access", tags={"Device SN"}, summary="Read current SN management capability",
     * security={{"Bearer":{}}}, @OA\Response(response=200, description="Current capability; no-store",
     * @OA\JsonContent(@OA\Property(property="success", type="boolean"),
     * @OA\Property(property="data", type="object", @OA\Property(property="allowed", type="boolean"),
     * @OA\Property(property="access_scope", type="string", nullable=true,
     * enum={"root-only", "admin-only", "manager-only", "auth-only"})))),
     * @OA\Response(response=401, description="Authentication required"),
     * @OA\Response(response=503, description="Authoritative plugin configuration unavailable")) */
    public function actionAccess()
    {
        return $this->ok($this->accessService()->resolve('sn-management', $this->authenticatedUser()));
    }

    /**
     * @OA\Get(path="/v1/plugin-sn", tags={"Device SN"}, summary="List masked SN records",
     *     security={{"Bearer":{}}}, @OA\Response(response=200, description="Paginated items"),
     *     @OA\Response(response=403, description="Current plugin configuration denies access"))
     */
    public function actionIndex()
    {
        $this->requireManagementAccess();
        [$page, $size] = $this->pagination();
        $filters = ['q' => $this->search(), 'status' => Yii::$app->request->get('status', '')];
        $userId = Yii::$app->request->get('user_id');
        if ($userId !== null && $userId !== '') {
            $filters['user_id'] = $this->positiveInt($userId, 'user_id');
        }
        return $this->ok($this->service()->listing($filters, $page, $size));
    }

    /** @OA\Get(path="/v1/plugin-sn/accounts", tags={"Device SN"}, summary="Search eligible accounts",
     * security={{"Bearer":{}}}, @OA\Response(response=200, description="Eligible ordinary accounts")) */
    public function actionAccounts()
    {
        $this->requireManagementAccess();
        [$page, $size] = $this->pagination();
        return $this->ok($this->service()->accounts($this->search(), $page, $size));
    }

    /** @OA\Get(path="/v1/plugin-sn/{id}", tags={"Device SN"}, summary="Read SN details and audit events",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * security={{"Bearer":{}}}, @OA\Response(response=200, description="Masked record and events")) */
    public function actionView($id)
    {
        $this->requireManagementAccess();
        return $this->ok($this->service()->view($this->positiveInt($id, 'id')));
    }

    /** @OA\Post(path="/v1/plugin-sn/generate", tags={"Device SN"}, summary="Generate 1–100 SN codes",
     * security={{"Bearer":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(required={"user_id"},
     * @OA\Property(property="user_id", type="integer"), @OA\Property(property="count", type="integer", default=1),
     * @OA\Property(property="remark", type="string"))),
     * @OA\Response(response=201, description="Generated records including complete SNs; no-store")) */
    public function actionGenerate()
    {
        $operator = $this->requireManagementAccess();
        $body = $this->body();
        if (array_diff(array_keys($body), ['user_id', 'count', 'remark'])) {
            throw new BadRequestHttpException('Unexpected generation field.');
        }
        $remark = $body['remark'] ?? '';
        if (!is_string($remark)) {
            throw new BadRequestHttpException('Remark must be a string.');
        }
        $items = $this->service()->generate($this->positiveInt($body['user_id'] ?? null, 'user_id'),
            $this->positiveInt($body['count'] ?? 1, 'count'), $remark, $operator);
        Yii::$app->response->statusCode = 201;
        return $this->ok(['items' => $items]);
    }

    /** @OA\Patch(path="/v1/plugin-sn/{id}", tags={"Device SN"}, summary="Enable, disable or annotate SN",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * security={{"Bearer":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(
     * @OA\Property(property="enabled", type="boolean"), @OA\Property(property="remark", type="string"))),
     * @OA\Response(response=200, description="Updated record; account and device are immutable")) */
    public function actionUpdate($id)
    {
        $operator = $this->requireManagementAccess();
        return $this->ok($this->service()->update($this->positiveInt($id, 'id'), $this->body(), $operator));
    }

    /** @OA\Post(path="/v1/plugin-sn/{id}/reveal", tags={"Device SN"}, summary="Reveal a complete SN with audit",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * security={{"Bearer":{}}}, @OA\Response(response=200, description="Complete SN; no-store")) */
    public function actionReveal($id)
    {
        $operator = $this->requireManagementAccess();
        return $this->ok($this->service()->reveal($this->positiveInt($id, 'id'), $operator));
    }

    /** @OA\Post(path="/v1/plugin-sn/export", tags={"Device SN"}, summary="Export selected complete SNs with audit",
     * security={{"Bearer":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(required={"ids"},
     * @OA\Property(property="ids", type="array", maxItems=100, @OA\Items(type="integer")))),
     * @OA\Response(response=200, description="Selected complete SN records; no-store")) */
    public function actionExport()
    {
        $operator = $this->requireManagementAccess();
        $body = $this->body();
        if (!isset($body['ids']) || !is_array($body['ids']) || !array_is_list($body['ids']) || array_diff(array_keys($body), ['ids'])) {
            throw new BadRequestHttpException('An ids array is required.');
        }
        return $this->ok(['items' => $this->service()->export($body['ids'], $operator)]);
    }

    private function body(): array
    {
        $body = Yii::$app->request->getBodyParams();
        if (!is_array($body)) {
            throw new BadRequestHttpException('A JSON object is required.');
        }
        return $body;
    }

    private function pagination(): array
    {
        $page = $this->positiveInt(Yii::$app->request->get('page', 1), 'page');
        $size = $this->positiveInt(Yii::$app->request->get('page_size', 20), 'page_size');
        return [min($page, 1000000), min($size, 100)];
    }

    private function positiveInt($value, string $field): int
    {
        if ((!is_int($value) && !(is_string($value) && preg_match('/^[1-9][0-9]{0,9}$/D', $value)))
            || (int)$value < 1 || (int)$value > 2147483647) {
            throw new BadRequestHttpException($field . ' must be a positive integer.');
        }
        return (int)$value;
    }

    private function search(): string
    {
        $q = Yii::$app->request->get('q', '');
        if (!is_string($q) || mb_strlen($q) > 255) {
            throw new BadRequestHttpException('Invalid search query.');
        }
        return trim($q);
    }

    private function ok(array $data): array
    {
        return ['success' => true, 'data' => $data];
    }
}
