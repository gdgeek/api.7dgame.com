<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\GroupVerse;
use api\modules\v1\models\File;
use api\modules\v1\models\Manager;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaCode;
use api\modules\v1\models\MetaResource;
use api\modules\v1\models\Organization;
use api\modules\v1\models\Resource;
use api\modules\v1\models\User;
use api\modules\v1\models\UserOrganization;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseCode;
use api\modules\v1\models\VerseMeta;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseSpace;
use api\modules\v1\models\VerseTags;
use api\modules\v1\services\ScenePackageService;
use bizley\jwt\JwtHttpBearerAuth;
use common\components\Storage;
use common\components\UuidHelper;
use common\components\security\PasswordPolicyValidator;
use mdm\admin\components\AccessControl;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Campus plugin organization-scoped account operations.
 *
 * This controller intentionally exposes a narrower surface than plugin-user:
 * no account creation, deletion, role edits, or organization binding edits.
 */
class PluginCampusController extends ScenePackageController
{
    private const ROLE_PRIORITY = [
        'root' => 4,
        'admin' => 3,
        'manager' => 2,
        'user' => 1,
    ];

    private const RESOURCE_TYPES = [
        'polygen',
        'voxel',
        'picture',
        'video',
        'audio',
        'particle',
        'file',
    ];

    private const MAX_RESOURCE_UPLOAD_BYTES = 209715200;
    private const MAX_RESOURCE_INFO_BYTES = 16384;

    private const RESOURCE_EXTENSIONS_BY_TYPE = [
        'polygen' => ['glb', 'gltf', 'fbx', 'obj', 'stl'],
        'voxel' => ['vox'],
        'picture' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'webm', 'mov'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'particle' => ['json'],
        'file' => ['pdf', 'zip', 'rar', '7z'],
    ];

    private const DANGEROUS_RESOURCE_EXTENSIONS = [
        'app',
        'apk',
        'asp',
        'aspx',
        'bat',
        'bash',
        'cgi',
        'cmd',
        'com',
        'css',
        'deb',
        'dll',
        'dmg',
        'dylib',
        'exe',
        'fish',
        'htm',
        'html',
        'ipa',
        'jar',
        'js',
        'jsp',
        'jspx',
        'mjs',
        'msi',
        'phar',
        'php',
        'phtml',
        'pl',
        'pkg',
        'ps1',
        'py',
        'rb',
        'rpm',
        'scr',
        'sh',
        'so',
        'svg',
        'swf',
        'vbs',
        'xhtml',
        'xml',
        'zsh',
    ];

    private const DANGEROUS_RESOURCE_MIME_TYPES = [
        'application/ecmascript',
        'application/javascript',
        'application/x-httpd-php',
        'application/x-msdownload',
        'application/x-php',
        'application/x-sh',
        'application/xhtml+xml',
        'image/svg+xml',
        'text/css',
        'text/html',
        'text/javascript',
        'text/xml',
    ];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_XML,
            '*/*' => Response::FORMAT_JSON,
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                [
                    'class' => JwtHttpBearerAuth::class,
                    'throwException' => false,
                ],
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'allowActions' => [
                'options',
                'users',
                'password',
                'clear-content-preview',
                'clear-content',
                'import-scene-zip',
                'upload-resource',
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'users' => ['GET'],
            'password' => ['POST'],
            'clear-content-preview' => ['POST'],
            'clear-content' => ['POST'],
            'import-scene-zip' => ['POST'],
            'upload-resource' => ['POST'],
        ];
    }

    /**
     * GET /v1/plugin-campus/users
     */
    public function actionUsers(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $actorContext = $this->resolveCampusActor();
        if (isset($actorContext['error'])) {
            return $actorContext['error'];
        }

        $request = Yii::$app->request;
        $organizationIds = $this->resolveOrganizationScopeForList(
            $actorContext['user'],
            $actorContext['roles'],
            $request->get('organization_id')
        );
        if (isset($organizationIds['error'])) {
            return $organizationIds['error'];
        }

        $page = max(1, (int)$request->get('page', 1));
        $pageSize = max(1, min(100, (int)$request->get('pageSize', $request->get('per-page', 20))));
        $search = trim((string)$request->get('search', ''));

        $users = $this->findManageableUsers(
            $actorContext['user'],
            $actorContext['roles'],
            $organizationIds,
            $search
        );

        $total = count($users);
        $pageUsers = array_slice($users, ($page - 1) * $pageSize, $pageSize);

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => array_map(fn(User $user): array => $this->serializeManagedUser($user), $pageUsers),
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
            ],
        ];
    }

    /**
     * POST /v1/plugin-campus/users/password
     */
    public function actionPassword(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetContext = $this->resolveTargetUsersFromBody();
        if (isset($targetContext['error'])) {
            return $targetContext['error'];
        }

        $password = (string)Yii::$app->request->getBodyParam('password', '');
        if ($password === '') {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4220, 'message' => 'password 不能为空'];
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($targetContext['users'] as $user) {
            $errors = $this->validatePasswordForUser($password, $user);
            if (!empty($errors)) {
                $failedCount++;
                $results[] = $this->operationResult($user, false, '密码不符合要求', ['errors' => $errors]);
                continue;
            }

            try {
                $user->setPassword($password);
                $user->updated_at = time();
                if (!$user->save(false, ['password_hash', 'updated_at'])) {
                    throw new \RuntimeException(json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE));
                }

                $successCount++;
                $results[] = $this->operationResult($user, true, '密码已更新');
            } catch (\Throwable $e) {
                Yii::warning([
                    'message' => 'Campus password update failed',
                    'user_id' => (int)$user->id,
                    'exception' => $e->getMessage(),
                ], __METHOD__);

                $failedCount++;
                $results[] = $this->operationResult($user, false, '密码更新失败');
            }
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ],
        ];
    }

    /**
     * POST /v1/plugin-campus/users/clear-content-preview
     */
    public function actionClearContentPreview(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetContext = $this->resolveTargetUsersFromBody();
        if (isset($targetContext['error'])) {
            return $targetContext['error'];
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => $this->buildClearContentPreview($targetContext['users']),
        ];
    }

    /**
     * POST /v1/plugin-campus/users/clear-content
     */
    public function actionClearContent(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetContext = $this->resolveTargetUsersFromBody();
        if (isset($targetContext['error'])) {
            return $targetContext['error'];
        }

        if (Yii::$app->request->getBodyParam('confirm') !== true) {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4220, 'message' => '清空资源和场景必须传入 confirm=true'];
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($targetContext['users'] as $user) {
            try {
                $cleared = $this->clearUserContent((int)$user->id);
                $successCount++;
                $results[] = $this->operationResult($user, true, '资源和场景已清空', ['cleared' => $cleared]);
            } catch (\Throwable $e) {
                Yii::error([
                    'message' => 'Campus clear content failed',
                    'user_id' => (int)$user->id,
                    'exception' => $e->getMessage(),
                ], __METHOD__);

                $failedCount++;
                $results[] = $this->operationResult($user, false, '清空失败', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ],
        ];
    }

    /**
     * POST /v1/plugin-campus/users/import-scene-zip
     */
    public function actionImportSceneZip(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetContext = $this->resolveTargetUsersFromBody();
        if (isset($targetContext['error'])) {
            return $targetContext['error'];
        }

        $data = $this->parseZipUpload();
        $this->validateImportData($data);
        $this->validateVersion($data);
        $this->validateFileIds($data);

        $service = new ScenePackageService();
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($targetContext['users'] as $user) {
            try {
                $importResult = $service->importScene($data, (int)$user->id);
                $successCount++;
                $results[] = $this->operationResult($user, true, '场景已导入', [
                    'verse_id' => $importResult['verseId'] ?? null,
                    'meta_id_map' => $importResult['metaIdMap'] ?? [],
                    'resource_id_map' => $importResult['resourceIdMap'] ?? [],
                ]);
            } catch (\Throwable $e) {
                Yii::error([
                    'message' => 'Campus scene import failed',
                    'user_id' => (int)$user->id,
                    'exception' => $e->getMessage(),
                ], __METHOD__);

                $failedCount++;
                $results[] = $this->operationResult($user, false, '导入失败', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ],
        ];
    }

    /**
     * POST /v1/plugin-campus/users/upload-resource
     */
    public function actionUploadResource(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetContext = $this->resolveTargetUsersFromBody();
        if (isset($targetContext['error'])) {
            return $targetContext['error'];
        }

        $upload = $this->resolveCampusResourceUpload();
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($targetContext['users'] as $user) {
            try {
                $created = $this->createCampusResourceForUser($user, $upload);
                $successCount++;
                $results[] = $this->operationResult($user, true, '资源已上传', $created);
            } catch (\Throwable $e) {
                Yii::error([
                    'message' => 'Campus resource upload failed',
                    'user_id' => (int)$user->id,
                    'exception' => $e->getMessage(),
                ], __METHOD__);

                $failedCount++;
                $results[] = $this->operationResult($user, false, '上传失败', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ],
        ];
    }

    private function resolveCampusActor(): array
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 2001, 'message' => '请先登录']];
        }

        $roles = $this->getRoleNamesByUserId((int)$user->id);
        if (!$this->hasAnyRole($roles, ['root', 'admin', 'manager'])) {
            Yii::$app->response->statusCode = 403;
            return ['error' => ['code' => 2003, 'message' => '没有权限执行此操作']];
        }

        return ['user' => $user, 'roles' => $roles];
    }

    private function resolveTargetUsersFromBody(): array
    {
        $actorContext = $this->resolveCampusActor();
        if (isset($actorContext['error'])) {
            return $actorContext;
        }

        $organizationId = $this->parsePositiveInt(Yii::$app->request->getBodyParam('organization_id'));
        if ($organizationId === null) {
            Yii::$app->response->statusCode = 422;
            return ['error' => ['code' => 4220, 'message' => 'organization_id 必须为正整数']];
        }

        $organizationError = $this->assertOrganizationAllowed(
            $actorContext['user'],
            $actorContext['roles'],
            $organizationId
        );
        if ($organizationError !== null) {
            return ['error' => $organizationError];
        }

        $userIdsResult = $this->parseUserIds(Yii::$app->request->getBodyParam('user_ids', []));
        if (isset($userIdsResult['error'])) {
            return $userIdsResult;
        }

        $manageableUsers = $this->findManageableUsers(
            $actorContext['user'],
            $actorContext['roles'],
            [$organizationId],
            ''
        );
        $manageableById = [];
        foreach ($manageableUsers as $user) {
            $manageableById[(int)$user->id] = $user;
        }

        $requestedUserIds = $userIdsResult['user_ids'];
        if (empty($requestedUserIds)) {
            $targets = array_values($manageableById);
        } else {
            $targets = [];
            foreach ($requestedUserIds as $userId) {
                if (!isset($manageableById[$userId])) {
                    Yii::$app->response->statusCode = 403;
                    return [
                        'error' => [
                            'code' => 2003,
                            'message' => '存在不可管理的目标账号',
                            'data' => ['user_id' => $userId],
                        ],
                    ];
                }
                $targets[] = $manageableById[$userId];
            }
        }

        if (empty($targets)) {
            Yii::$app->response->statusCode = 422;
            return ['error' => ['code' => 4221, 'message' => '没有可操作的目标账号']];
        }

        return [
            'actor' => $actorContext['user'],
            'roles' => $actorContext['roles'],
            'organization_id' => $organizationId,
            'users' => $targets,
        ];
    }

    private function resolveOrganizationScopeForList(User $actor, array $actorRoles, mixed $rawOrganizationId): array
    {
        $organizationId = $this->parsePositiveInt($rawOrganizationId);
        if ($rawOrganizationId !== null && $rawOrganizationId !== '' && $organizationId === null) {
            Yii::$app->response->statusCode = 422;
            return ['error' => ['code' => 4220, 'message' => 'organization_id 必须为正整数']];
        }

        if ($organizationId === null) {
            Yii::$app->response->statusCode = 422;
            return ['error' => ['code' => 4220, 'message' => 'organization_id 必须为正整数']];
        }

        $error = $this->assertOrganizationAllowed($actor, $actorRoles, $organizationId);
        if ($error !== null) {
            return ['error' => $error];
        }

        return [$organizationId];
    }

    private function assertOrganizationAllowed(User $actor, array $actorRoles, int $organizationId): ?array
    {
        if (!Organization::find()->where(['id' => $organizationId])->exists()) {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4220, 'message' => '组织不存在'];
        }

        if ($this->hasAnyRole($actorRoles, ['root'])) {
            return null;
        }

        if (!in_array($organizationId, $this->getOrganizationIdsByUserId((int)$actor->id), true)) {
            Yii::$app->response->statusCode = 403;
            return ['code' => 2003, 'message' => '不能管理非本组织账号'];
        }

        return null;
    }

    /**
     * @return User[]
     */
    private function findManageableUsers(User $actor, array $actorRoles, array $organizationIds, string $search): array
    {
        $query = User::find()
            ->alias('u')
            ->distinct()
            ->orderBy(['u.username' => SORT_ASC, 'u.id' => SORT_ASC]);

        if (!empty($organizationIds)) {
            $query->innerJoin(['uo' => UserOrganization::tableName()], 'uo.user_id = u.id')
                ->andWhere(['uo.organization_id' => $organizationIds]);
        }

        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'u.username', $search],
                ['like', 'u.nickname', $search],
                ['like', 'u.email', $search],
            ]);
        }

        $users = $query->all();
        return array_values(array_filter(
            $users,
            fn(User $user): bool => (int)$user->id !== (int)$actor->id
                && $this->canManageTargetRoles($actorRoles, $this->getRoleNamesByUserId((int)$user->id))
        ));
    }

    private function canManageTargetRoles(array $actorRoles, array $targetRoles): bool
    {
        if ($this->hasAnyRole($targetRoles, ['root'])) {
            return false;
        }

        if ($this->hasAnyRole($actorRoles, ['root'])) {
            return $this->hasAnyRole($targetRoles, ['admin', 'manager', 'user']);
        }

        if ($this->hasAnyRole($actorRoles, ['admin', 'manager'])) {
            return !$this->hasAnyRole($targetRoles, ['admin'])
                && $this->hasAnyRole($targetRoles, ['manager', 'user']);
        }

        return false;
    }

    private function serializeManagedUser(User $user): array
    {
        $roles = $this->getRoleNamesByUserId((int)$user->id);

        return [
            'id' => (int)$user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'roles' => $roles,
            'primary_role' => $this->highestRole($roles),
            'organizations' => User::normalizeOrganizations($user->organizations ?? []),
            'created_at' => $user->created_at,
            'content_counts' => $this->countUserContent((int)$user->id),
        ];
    }

    private function buildClearContentPreview(array $users): array
    {
        $details = [];
        $verseCount = 0;
        $resourceCount = 0;
        $metaCount = 0;

        foreach ($users as $user) {
            $counts = $this->countUserContent((int)$user->id);
            $verseCount += $counts['verse_count'];
            $resourceCount += $counts['resource_count'];
            $metaCount += $counts['meta_count'];
            $details[] = [
                'user_id' => (int)$user->id,
                'username' => $user->username,
                ...$counts,
            ];
        }

        return [
            'user_count' => count($users),
            'verse_count' => $verseCount,
            'resource_count' => $resourceCount,
            'meta_count' => $metaCount,
            'targets' => $details,
        ];
    }

    private function countUserContent(int $userId): array
    {
        return [
            'verse_count' => (int)Verse::find()->where(['author_id' => $userId])->count(),
            'resource_count' => (int)Resource::find()->where(['author_id' => $userId])->count(),
            'meta_count' => (int)Meta::find()->where(['author_id' => $userId])->count(),
        ];
    }

    private function clearUserContent(int $userId): array
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $verseIds = array_map('intval', Verse::find()->select('id')->where(['author_id' => $userId])->column());
            $metaIds = array_map('intval', Meta::find()->select('id')->where(['author_id' => $userId])->column());
            $resourceIds = array_map('intval', Resource::find()->select('id')->where(['author_id' => $userId])->column());

            if (!empty($verseIds)) {
                $linkedMetaIds = VerseMeta::find()
                    ->alias('vm')
                    ->innerJoin(['m' => Meta::tableName()], 'm.id = vm.meta_id')
                    ->select('vm.meta_id')
                    ->where(['vm.verse_id' => $verseIds, 'm.author_id' => $userId])
                    ->column();
                $metaIds = array_values(array_unique(array_merge($metaIds, array_map('intval', $linkedMetaIds))));
            }

            if (!empty($metaIds)) {
                $linkedResourceIds = MetaResource::find()
                    ->alias('mr')
                    ->innerJoin(['r' => Resource::tableName()], 'r.id = mr.resource_id')
                    ->select('mr.resource_id')
                    ->where(['mr.meta_id' => $metaIds, 'r.author_id' => $userId])
                    ->column();
                $resourceIds = array_values(array_unique(array_merge($resourceIds, array_map('intval', $linkedResourceIds))));
            }

            $deletedVerses = 0;
            $deletedMetas = 0;
            $deletedResources = 0;

            if (!empty($verseIds)) {
                Manager::deleteAll(['verse_id' => $verseIds]);
                VerseCode::deleteAll(['verse_id' => $verseIds]);
                VerseTags::deleteAll(['verse_id' => $verseIds]);
                VerseProperty::deleteAll(['verse_id' => $verseIds]);
                VerseSpace::deleteAll(['verse_id' => $verseIds]);
                GroupVerse::deleteAll(['verse_id' => $verseIds]);
                VerseMeta::deleteAll(['verse_id' => $verseIds]);
                Verse::bumpGroupVerseRevision();
                $deletedVerses = Verse::deleteAll(['id' => $verseIds]);
            }

            if (!empty($metaIds)) {
                MetaCode::deleteAll(['meta_id' => $metaIds]);
                MetaResource::deleteAll(['meta_id' => $metaIds]);
                VerseMeta::deleteAll(['meta_id' => $metaIds]);
                $deletedMetas = Meta::deleteAll(['id' => $metaIds]);
            }

            if (!empty($resourceIds)) {
                MetaResource::deleteAll(['resource_id' => $resourceIds]);
                $resources = Resource::find()->where(['id' => $resourceIds])->all();
                foreach ($resources as $resource) {
                    if ($resource->delete() !== false) {
                        $deletedResources++;
                    }
                }
            }

            $transaction->commit();

            return [
                'verse_count' => $deletedVerses,
                'resource_count' => $deletedResources,
                'meta_count' => $deletedMetas,
            ];
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }

    private function storeCampusResourceUpload(): array
    {
        $uploadedFile = UploadedFile::getInstanceByName('file');
        if ($uploadedFile === null || $uploadedFile->error !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException('没有上传资源文件');
        }
        if ((int)$uploadedFile->size <= 0) {
            throw new BadRequestHttpException('非法文件大小');
        }

        $filename = $this->normalizeUploadFilename(
            Yii::$app->request->getBodyParam('filename', $uploadedFile->name)
        );
        $resourceName = $this->normalizeResourceName(
            Yii::$app->request->getBodyParam('name', pathinfo($filename, PATHINFO_FILENAME))
        );
        $resourceType = $this->normalizeResourceType(
            Yii::$app->request->getBodyParam('type', ''),
            $uploadedFile
        );
        $this->assertResourceUploadPolicy($filename, $resourceType, $uploadedFile);
        $info = $this->normalizeResourceInfo(Yii::$app->request->getBodyParam('info', null));

        $storage = new Storage();
        if (!$storage->init()) {
            throw new ServerErrorHttpException('文件系统初始化失败');
        }

        $bucket = 'store';
        $directory = $this->resourceStorageDirectory($resourceType);
        $md5 = md5_file($uploadedFile->tempName);
        if (!is_string($md5) || !preg_match('/^[a-f0-9]{32}$/', $md5)) {
            throw new BadRequestHttpException('非法 MD5');
        }

        $storedFilename = $md5 . $this->safeExtensionSuffix($filename);
        $storage->targetDirector($bucket, $directory);
        $key = $directory . '/' . $storedFilename;
        $target = $storage->path($bucket, $key);

        if (!is_file($target) && !$uploadedFile->saveAs($target, false)) {
            throw new ServerErrorHttpException('保存资源文件失败');
        }

        return [
            'bucket' => $bucket,
            'key' => $key,
            'url' => $storage->publicUrl($bucket, $key),
            'filename' => $filename,
            'size' => (int)(filesize($target) ?: $uploadedFile->size),
            'md5' => $md5,
            'mime_type' => $uploadedFile->type ?: 'application/octet-stream',
            'resource_name' => $resourceName,
            'resource_type' => $resourceType,
            'info' => $info,
        ];
    }

    private function resolveCampusResourceUpload(): array
    {
        if (UploadedFile::getInstanceByName('file') !== null) {
            return $this->storeCampusResourceUpload();
        }

        return $this->resourceUploadPayloadFromBody();
    }

    private function resourceUploadPayloadFromBody(): array
    {
        $filePayload = Yii::$app->request->getBodyParam('file', []);
        if (!is_array($filePayload)) {
            $filePayload = [];
        }

        $value = static function (string $key, mixed $default = null) use ($filePayload): mixed {
            return $filePayload[$key] ?? Yii::$app->request->getBodyParam($key, $default);
        };

        $storage = new Storage();
        if (!$storage->init()) {
            throw new ServerErrorHttpException('文件系统初始化失败');
        }

        $key = $storage->normalizeKey($value('key', ''));
        $filename = $this->normalizeUploadFilename($value('filename', basename($key)));
        $md5 = strtolower(trim((string)$value('md5', '')));
        $size = $this->parsePositiveInt($value('size', null));
        if ($size === null) {
            throw new BadRequestHttpException('非法文件大小');
        }

        $resourceName = $this->normalizeResourceName(
            Yii::$app->request->getBodyParam('name', pathinfo($filename, PATHINFO_FILENAME))
        );
        $resourceType = $this->normalizeResourceTypeFromFilename(
            Yii::$app->request->getBodyParam('type', ''),
            $filename
        );
        $this->assertResourceMetadataPolicy($filename, $resourceType, $key, $md5, $size);

        return [
            'bucket' => 'store',
            'key' => $key,
            'url' => $storage->publicUrl('store', $key),
            'filename' => $filename,
            'size' => $size,
            'md5' => $md5,
            'mime_type' => $this->normalizeMimeType($value('mime_type', $value('type_mime', 'application/octet-stream'))),
            'resource_name' => $resourceName,
            'resource_type' => $resourceType,
            'info' => $this->normalizeResourceInfo(Yii::$app->request->getBodyParam('info', null)),
        ];
    }

    private function createCampusResourceForUser(User $user, array $upload): array
    {
        $previousIdentity = Yii::$app->user->identity ?? null;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            Yii::$app->user->setIdentity($user);

            $file = File::find()
                ->where([
                    'user_id' => (int)$user->id,
                    'key' => $upload['key'],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($file === null) {
                $file = new File([
                    'url' => $upload['url'],
                    'filename' => $upload['filename'],
                    'key' => $upload['key'],
                    'md5' => $upload['md5'],
                    'type' => $upload['mime_type'],
                    'size' => $upload['size'],
                    'user_id' => (int)$user->id,
                ]);
                if (!$file->save()) {
                    throw new \RuntimeException('创建文件记录失败: ' . json_encode($file->getErrors(), JSON_UNESCAPED_UNICODE));
                }
            }

            $resource = new Resource([
                'name' => $upload['resource_name'],
                'type' => $upload['resource_type'],
                'uuid' => UuidHelper::uuid(),
                'info' => $upload['info'],
                'file_id' => (int)$file->id,
                'author_id' => (int)$user->id,
                'updater_id' => (int)$user->id,
            ]);
            if (!$resource->save()) {
                throw new \RuntimeException('创建资源记录失败: ' . json_encode($resource->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            $transaction->commit();

            return [
                'file_id' => (int)$file->id,
                'resource_id' => (int)$resource->id,
                'resource_name' => $resource->name,
                'resource_type' => $resource->type,
            ];
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        } finally {
            Yii::$app->user->setIdentity($previousIdentity);
        }
    }

    private function normalizeUploadFilename(mixed $filename): string
    {
        $filename = trim((string)$filename);
        if ($filename === '' || preg_match('/[\x00-\x1F\x7F]/', $filename)) {
            throw new BadRequestHttpException('非法文件名');
        }
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false || strpos($filename, '..') !== false) {
            throw new BadRequestHttpException('非法文件名');
        }

        return $this->limitString($filename, 255);
    }

    private function normalizeResourceName(mixed $name): string
    {
        $resourceName = trim((string)$name);
        if ($resourceName === '') {
            $resourceName = '未命名资源';
        }

        return $this->limitString($resourceName, 255);
    }

    private function normalizeResourceType(mixed $type, UploadedFile $uploadedFile): string
    {
        $resourceType = strtolower(trim((string)$type));
        if ($resourceType === '') {
            $resourceType = $this->inferResourceType($uploadedFile);
        }

        if (!in_array($resourceType, self::RESOURCE_TYPES, true)) {
            throw new BadRequestHttpException('非法资源类型');
        }

        return $resourceType;
    }

    private function normalizeResourceTypeFromFilename(mixed $type, string $filename): string
    {
        $resourceType = strtolower(trim((string)$type));
        if ($resourceType === '') {
            $resourceType = $this->inferResourceTypeFromExtension(
                strtolower(pathinfo($filename, PATHINFO_EXTENSION))
            );
        }

        if (!in_array($resourceType, self::RESOURCE_TYPES, true)) {
            throw new BadRequestHttpException('非法资源类型');
        }

        return $resourceType;
    }

    private function normalizeResourceInfo(mixed $info): ?string
    {
        if ($info === null || $info === '') {
            return null;
        }

        if (is_array($info)) {
            $encoded = json_encode($info, JSON_UNESCAPED_UNICODE);
            if ($encoded === false) {
                throw new BadRequestHttpException('资源说明格式不合法');
            }
            $info = $encoded;
        }

        $normalized = (string)$info;
        if (strlen($normalized) > self::MAX_RESOURCE_INFO_BYTES) {
            throw new BadRequestHttpException('资源说明过长');
        }

        return $normalized;
    }

    private function assertResourceUploadPolicy(string $filename, string $resourceType, UploadedFile $uploadedFile): void
    {
        $size = (int)$uploadedFile->size;
        if ($size <= 0) {
            throw new BadRequestHttpException('非法文件大小');
        }
        if ($size > self::MAX_RESOURCE_UPLOAD_BYTES) {
            throw new BadRequestHttpException('资源文件不能超过 200MB');
        }

        if ($this->hasDoubleExtension($filename)) {
            throw new BadRequestHttpException('资源文件不允许使用多重扩展名');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension === '') {
            throw new BadRequestHttpException('资源文件必须包含扩展名');
        }
        if (in_array($extension, self::DANGEROUS_RESOURCE_EXTENSIONS, true)) {
            throw new BadRequestHttpException('不支持的资源文件类型');
        }

        $allowedExtensions = self::RESOURCE_EXTENSIONS_BY_TYPE[$resourceType] ?? [];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new BadRequestHttpException('资源文件类型与资源分类不匹配');
        }

        $mimeType = strtolower(trim((string)$uploadedFile->type));
        if ($mimeType !== '' && in_array($mimeType, self::DANGEROUS_RESOURCE_MIME_TYPES, true)) {
            throw new BadRequestHttpException('不支持的资源文件类型');
        }

        if ($resourceType === 'picture') {
            $this->assertPictureUploadContent($uploadedFile);
        }
    }

    private function assertResourceMetadataPolicy(
        string $filename,
        string $resourceType,
        string $key,
        string $md5,
        int $size
    ): void {
        if ($size <= 0) {
            throw new BadRequestHttpException('非法文件大小');
        }
        if ($size > self::MAX_RESOURCE_UPLOAD_BYTES) {
            throw new BadRequestHttpException('资源文件不能超过 200MB');
        }
        if (!preg_match('/^[a-f0-9]{32}$/', $md5)) {
            throw new BadRequestHttpException('非法 MD5');
        }
        if ($this->hasDoubleExtension($filename)) {
            throw new BadRequestHttpException('资源文件不允许使用多重扩展名');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $keyExtension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        if ($extension === '' || $keyExtension === '') {
            throw new BadRequestHttpException('资源文件必须包含扩展名');
        }
        if ($extension !== $keyExtension) {
            throw new BadRequestHttpException('资源文件扩展名与存储 key 不一致');
        }
        if (basename($key) !== $md5 . '.' . $extension) {
            throw new BadRequestHttpException('资源存储 key 必须使用 md5 和扩展名');
        }
        if (in_array($extension, self::DANGEROUS_RESOURCE_EXTENSIONS, true)) {
            throw new BadRequestHttpException('不支持的资源文件类型');
        }

        $allowedExtensions = self::RESOURCE_EXTENSIONS_BY_TYPE[$resourceType] ?? [];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new BadRequestHttpException('资源文件类型与资源分类不匹配');
        }
    }

    private function normalizeMimeType(mixed $mimeType): string
    {
        $normalized = strtolower(trim((string)$mimeType));
        if ($normalized === '') {
            return 'application/octet-stream';
        }
        if (in_array($normalized, self::DANGEROUS_RESOURCE_MIME_TYPES, true)) {
            throw new BadRequestHttpException('不支持的资源文件类型');
        }

        return $this->limitString($normalized, 255);
    }

    private function assertPictureUploadContent(UploadedFile $uploadedFile): void
    {
        $allowedImageMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $declaredMimeType = strtolower(trim((string)$uploadedFile->type));
        if ($declaredMimeType !== '' && !in_array($declaredMimeType, $allowedImageMimeTypes, true)) {
            throw new BadRequestHttpException('图片文件类型不合法');
        }

        if (empty($uploadedFile->tempName) || !is_file($uploadedFile->tempName) || !is_readable($uploadedFile->tempName)) {
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new BadRequestHttpException('无法校验图片内容');
        }

        $detectedMimeType = finfo_file($finfo, $uploadedFile->tempName);
        finfo_close($finfo);

        if ($detectedMimeType === false || !in_array(strtolower($detectedMimeType), $allowedImageMimeTypes, true)) {
            throw new BadRequestHttpException('图片内容与文件类型不匹配');
        }
    }

    private function inferResourceType(UploadedFile $uploadedFile): string
    {
        $mimeType = strtolower((string)$uploadedFile->type);
        $extension = strtolower(pathinfo((string)$uploadedFile->name, PATHINFO_EXTENSION));

        if (str_starts_with($mimeType, 'image/')) {
            return 'picture';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        if ($extension === 'vox') {
            return 'voxel';
        }
        if (in_array($extension, ['glb', 'gltf', 'fbx', 'obj', 'stl'], true)) {
            return 'polygen';
        }

        return 'file';
    }

    private function inferResourceTypeFromExtension(string $extension): string
    {
        foreach (self::RESOURCE_EXTENSIONS_BY_TYPE as $resourceType => $extensions) {
            if (in_array($extension, $extensions, true)) {
                return $resourceType;
            }
        }

        return 'file';
    }

    private function resourceStorageDirectory(string $resourceType): string
    {
        return match ($resourceType) {
            'polygen' => 'polygen',
            'voxel' => 'voxel',
            'picture' => 'picture',
            'video' => 'video',
            'audio' => 'audio',
            'particle' => 'particle',
            default => 'file',
        };
    }

    private function safeExtensionSuffix(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension === '' || !preg_match('/^[a-z0-9]{1,16}$/', $extension)) {
            return '';
        }

        return '.' . $extension;
    }

    private function hasDoubleExtension(string $filename): bool
    {
        $basename = basename(str_replace('\\', '/', $filename));
        $withoutLastExtension = pathinfo($basename, PATHINFO_FILENAME);

        return strpos($withoutLastExtension, '.') !== false;
    }

    private function limitString(string $value, int $limit): string
    {
        if (function_exists('mb_strcut')) {
            return mb_strcut($value, 0, $limit, 'UTF-8');
        }

        return substr($value, 0, $limit);
    }

    private function getRoleNamesByUserId(int $userId): array
    {
        $roles = Yii::$app->authManager->getRolesByUser($userId);

        return array_values(array_unique(array_map('strval', array_keys($roles))));
    }

    private function hasAnyRole(array $roles, array $expectedRoles): bool
    {
        foreach ($expectedRoles as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        return false;
    }

    private function highestRole(array $roles): string
    {
        $highestRole = 'user';
        foreach ($roles as $role) {
            if ((self::ROLE_PRIORITY[$role] ?? 0) > (self::ROLE_PRIORITY[$highestRole] ?? 0)) {
                $highestRole = $role;
            }
        }

        return $highestRole;
    }

    private function getOrganizationIdsByUserId(int $userId): array
    {
        return array_map('intval', UserOrganization::find()
            ->select('organization_id')
            ->where(['user_id' => $userId])
            ->column());
    }

    private function parsePositiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $intValue = (int)$value;
            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    private function parseUserIds(mixed $rawUserIds): array
    {
        if ($rawUserIds === null || $rawUserIds === '' || $rawUserIds === []) {
            return ['user_ids' => []];
        }

        if (is_string($rawUserIds)) {
            $trimmed = trim($rawUserIds);
            if ($trimmed === '') {
                return ['user_ids' => []];
            }

            $decoded = json_decode($trimmed, true);
            $rawUserIds = is_array($decoded) ? $decoded : explode(',', $trimmed);
        }

        if (!is_array($rawUserIds)) {
            Yii::$app->response->statusCode = 422;
            return ['error' => ['code' => 4220, 'message' => 'user_ids 必须为数组']];
        }

        $userIds = [];
        foreach ($rawUserIds as $rawUserId) {
            $userId = $this->parsePositiveInt($rawUserId);
            if ($userId === null) {
                Yii::$app->response->statusCode = 422;
                return ['error' => ['code' => 4220, 'message' => 'user_ids 仅允许正整数']];
            }
            $userIds[] = $userId;
        }

        return ['user_ids' => array_values(array_unique($userIds))];
    }

    private function validatePasswordForUser(string $password, User $user): array
    {
        $validator = new PasswordPolicyValidator();
        $result = $validator->validate($password, [
            'username' => $user->username,
            'email' => $user->email,
        ]);

        return $result['valid'] ? [] : $result['errors'];
    }

    private function operationResult(User $user, bool $success, string $message, array $extra = []): array
    {
        return [
            'user_id' => (int)$user->id,
            'username' => $user->username,
            'success' => $success,
            'message' => $message,
            ...$extra,
        ];
    }
}
