<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\components\PluginUserRolePolicy;
use api\modules\v1\services\EmailService;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use Yii;
use yii\web\Response;

/**
 * PluginUserController 提供用户管理插件的所有 API
 *
 * 与 PluginController 完全独立，自行实现 JWT 认证。
 * 路由前缀：/v1/plugin-user/
 */
class PluginUserController extends \yii\rest\Controller
{
    private const PLUGIN_NAME = 'user-management';

    private const ROLE_LEVELS = [
        'root' => 4,
        'admin' => 3,
        'manager' => 2,
        'user' => 1,
    ];

    private const VALID_ROLES = ['root', 'admin', 'manager', 'user'];

    /**
     * {@inheritdoc}
     * 使用标准 JWT 认证 + RBAC 权限控制
     * check-invitation / register-send-code / register 为公开接口，无需认证
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options', 'check-invitation', 'register-send-code', 'register'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'me' => ['GET'],
            'users' => ['GET'],
            'create-user' => ['POST'],
            'update-user' => ['POST'],
            'delete-user' => ['POST'],
            'change-role' => ['POST'],
            'invitations' => ['GET'],
            'create-invitation' => ['POST'],
            'delete-invitation' => ['POST'],
            'check-invitation' => ['GET'],
            'invitation-records' => ['GET'],
            'register-send-code' => ['POST'],
            'register' => ['POST'],
            'batch-create-users' => ['POST'],
        ];
    }

    /**
     * 获取当前已认证用户及其角色
     */
    protected function resolveUser(): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));

        return ['user' => $user, 'roles' => $roles];
    }

    /**
     * 合并认证 + 权限检查
     */
    protected function resolveUserWithPermission($action): array
    {
        $result = $this->resolveUser();
        if (isset($result['error'])) {
            return $result;
        }

        $allowed = Yii::$app->authManager->checkAccess($result['user']->id, self::PLUGIN_NAME . '.' . $action);
        if (!$allowed) {
            Yii::$app->response->statusCode = 403;
            return ['error' => ['code' => 2003, 'message' => '没有权限执行此操作']];
        }

        return $result;
    }

    /**
     * 角色层级计算
     */
    protected function getRoleLevel($roles): int
    {
        return PluginUserRolePolicy::getRoleLevel($roles);
    }

    // ==================== 用户管理 CRUD ====================

    /**
     * GET /v1/plugin-user/me
     * 当前登录用户信息
     */
    public function actionMe()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUser();
        if (isset($result['error'])) {
            return $result['error'];
        }

        /** @var User $user */
        $user = $result['user'];
        $roles = $result['roles'];

        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname ?? $user->username,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $roles,
        ];
    }

    /**
     * GET /v1/plugin-user/users
     * 用户列表（分页、搜索）或单用户详情（?id=xxx）
     */
    public function actionUsers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $id = $request->get('id');

        // 单用户详情
        if ($id) {
            $result = $this->resolveUserWithPermission('view-user');
            if (isset($result['error'])) {
                return $result['error'];
            }

            $user = User::findOne($id);
            if (!$user) {
                Yii::$app->response->statusCode = 404;
                return ['code' => 4004, 'message' => '用户不存在'];
            }

            $roles = array_keys(Yii::$app->authManager->getRolesByUser($id));
            return [
                'code' => 0,
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'roles' => $roles,
                ],
            ];
        }

        // 用户列表
        $result = $this->resolveUserWithPermission('list-users');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $page = max(1, (int)$request->get('page', 1));
        $pageSize = min(100, max(1, (int)$request->get('pageSize', 20)));
        $search = $request->get('search', '');
        $status = $request->get('status');

        $query = User::find();
        if ($search) {
            $query->andWhere(['or',
                ['like', 'username', $search],
                ['like', 'email', $search],
            ]);
        }
        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => (int)$status]);
        }

        // 排序支持
        $sortField = $request->get('sort', 'id');
        $sortOrder = strtolower($request->get('order', 'desc')) === 'asc' ? SORT_ASC : SORT_DESC;
        $allowedSortFields = ['id', 'username', 'nickname', 'email', 'created_at'];

        if ($sortField === 'roles') {
            // 先算 total（在加 JOIN/GROUP BY 之前）
            $total = $query->count();
            // 按角色等级排序：LEFT JOIN auth_assignment，用 CASE 映射角色等级
            $query->leftJoin('auth_assignment', '{{auth_assignment}}.[[user_id]] = {{user}}.[[id]]');
            $orderExpr = new \yii\db\Expression(
                "MAX(CASE {{auth_assignment}}.[[item_name]] " .
                "WHEN 'root' THEN 4 WHEN 'admin' THEN 3 WHEN 'manager' THEN 2 WHEN 'user' THEN 1 ELSE 0 END) " .
                ($sortOrder === SORT_ASC ? "ASC" : "DESC")
            );
            $query->groupBy('{{user}}.[[id]]');
            $users = $query->orderBy($orderExpr)
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->all();
        } else {
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'id';
            }
            $total = $query->count();
            $users = $query->orderBy([$sortField => $sortOrder])
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->all();
        }

        $data = [];
        foreach ($users as $u) {
            $roles = array_keys(Yii::$app->authManager->getRolesByUser($u->id));
            $data[] = [
                'id' => $u->id,
                'username' => $u->username,
                'nickname' => $u->nickname,
                'email' => $u->email,
                'status' => $u->status,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
                'roles' => $roles,
            ];
        }

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => (int)$total,
                'totalPages' => (int)ceil($total / $pageSize),
            ],
        ];
    }

    /**
     * POST /v1/plugin-user/create-user
     */
    public function actionCreateUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('create-user');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $username = $request->getBodyParam('username');
        $password = $request->getBodyParam('password');
        $email = $request->getBodyParam('email', '');
        $status = $request->getBodyParam('status', 10);

        if (empty($username) || empty($password)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '用户名和密码不能为空'];
        }

        $existing = User::findOne(['username' => $username]);
        if ($existing) {
            Yii::$app->response->statusCode = 409;
            return ['code' => 4002, 'message' => '用户名已存在'];
        }

        $now = time();
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->status = (int)$status;
        $user->created_at = $now;
        $user->updated_at = $now;

        if (!$user->save(false)) {
            Yii::$app->response->statusCode = 500;
            return ['code' => 5000, 'message' => '创建用户失败'];
        }

        // 分配默认角色 user
        $authManager = Yii::$app->authManager;
        $roleObj = $authManager->getRole('user');
        if ($roleObj) {
            $authManager->assign($roleObj, $user->id);
        }

        return [
            'code' => 0,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => ['user'],
            ],
        ];
    }

    /**
     * 批量创建用户
     * POST /v1/plugin-user/batch-create-users
     *
     * 请求体: { "users": [ { "username", "nickname", "password", "role", "status" }, ... ] }
     * 最多 100 条，逐条创建，部分失败不影响其余。
     */
    public function actionBatchCreateUsers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('create-user');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $users = $request->getBodyParam('users', []);

        if (!is_array($users) || count($users) < 1 || count($users) > 100) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '用户数组长度必须在 1-100 之间'];
        }

        $operatorLevel = $this->getRoleLevel($result['roles']);
        $authManager = Yii::$app->authManager;
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $index => $userData) {
            $username = trim($userData['username'] ?? '');
            $nickname = trim($userData['nickname'] ?? '');
            $password = $userData['password'] ?? '';
            $role = $userData['role'] ?? '';
            $status = isset($userData['status']) ? (int)$userData['status'] : 10;

            // 校验 username
            if (empty($username) || mb_strlen($username) > 64) {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '用户名不能为空且不超过64字符'];
                $failedCount++;
                continue;
            }

            // 校验 password
            if (strlen($password) < 6) {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '密码不少于6位'];
                $failedCount++;
                continue;
            }

            // 校验 role 合法且非 root
            if (!in_array($role, self::VALID_ROLES) || $role === 'root') {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '角色无效或不允许分配 root'];
                $failedCount++;
                continue;
            }

            // 校验 role 级别不超过操作者
            $roleLevel = self::ROLE_LEVELS[$role] ?? 0;
            if ($roleLevel > $operatorLevel) {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '不能分配高于自身的角色'];
                $failedCount++;
                continue;
            }

            // 检查 username 唯一性
            $existing = User::findOne(['username' => $username]);
            if ($existing) {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '用户名已存在'];
                $failedCount++;
                continue;
            }

            // 创建用户
            $now = time();
            $user = new User();
            $user->username = $username;
            $user->nickname = $nickname;
            $user->email = '';
            $user->password_hash = Yii::$app->security->generatePasswordHash($password);
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->status = $status;
            $user->created_at = $now;
            $user->updated_at = $now;

            if (!$user->save(false)) {
                $results[] = ['index' => $index, 'username' => $username, 'success' => false, 'error' => '创建用户失败'];
                $failedCount++;
                continue;
            }

            // 分配角色
            $roleObj = $authManager->getRole($role);
            if ($roleObj) {
                $authManager->assign($roleObj, $user->id);
            }

            $results[] = ['index' => $index, 'username' => $username, 'success' => true, 'id' => $user->id];
            $successCount++;
        }

        return [
            'code' => 0,
            'data' => [
                'total' => count($users),
                'success' => $successCount,
                'failed' => $failedCount,
                'results' => $results,
            ],
        ];
    }


    /**
     * POST /v1/plugin-user/update-user
     */
    public function actionUpdateUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('update-user');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $id = $request->getBodyParam('id');
        $user = User::findOne($id);
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '用户不存在'];
        }

        $updated = false;
        $username = $request->getBodyParam('username');
        $email = $request->getBodyParam('email');
        $status = $request->getBodyParam('status');
        $password = $request->getBodyParam('password');

        if ($username !== null) { $user->username = $username; $updated = true; }
        if ($email !== null) { $user->email = $email; $updated = true; }
        if ($status !== null) { $user->status = (int)$status; $updated = true; }
        if (!empty($password)) {
            $user->password_hash = Yii::$app->security->generatePasswordHash($password);
            $updated = true;
        }

        if (!$updated) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '没有要更新的字段'];
        }

        $user->updated_at = time();
        $user->save(false);

        return [
            'code' => 0,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ];
    }

    /**
     * POST /v1/plugin-user/delete-user
     */
    public function actionDeleteUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('delete-user');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $id = Yii::$app->request->getBodyParam('id');
        $user = User::findOne($id);
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '用户不存在'];
        }

        $user->delete();

        return ['code' => 0, 'message' => '删除成功'];
    }

    /**
     * POST /v1/plugin-user/change-role
     */
    public function actionChangeRole()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('change-role');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $id = $request->getBodyParam('id');
        $role = $request->getBodyParam('role');

        if (!$role || !in_array($role, self::VALID_ROLES, true)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '无效的角色，有效值: ' . implode(', ', self::VALID_ROLES)];
        }

        if ($role === 'root') {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '不允许通过此接口分配 root 角色'];
        }

        $targetUser = User::findOne($id);
        if (!$targetUser) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '用户不存在'];
        }

        $targetRoles = array_keys(Yii::$app->authManager->getRolesByUser($id));
        $policyError = PluginUserRolePolicy::validateRoleChange($result['roles'], $targetRoles, $role);
        if ($policyError !== null) {
            Yii::$app->response->statusCode = in_array($policyError['code'], [2004, 2005, 2006], true) ? 403 : 400;
            return $policyError;
        }

        $authManager = Yii::$app->authManager;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 移除所有现有角色
            $authManager->revokeAll($id);
            // 分配新角色
            $roleObj = $authManager->getRole($role);
            if ($roleObj) {
                $authManager->assign($roleObj, $id);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return ['code' => 5000, 'message' => '角色修改失败'];
        }

        return [
            'code' => 0,
            'data' => [
                'id' => $targetUser->id,
                'username' => $targetUser->username,
                'email' => $targetUser->email,
                'status' => $targetUser->status,
                'created_at' => $targetUser->created_at,
                'updated_at' => $targetUser->updated_at,
                'roles' => [$role],
            ],
        ];
    }

    // ==================== 邀请系统 ====================

    /**
     * GET /v1/plugin-user/invitations
     */
    public function actionInvitations()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-invitations');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $redis = Yii::$app->redis;
        $cursor = 0;
        $keys = [];
        do {
            $scanResult = $redis->executeCommand('SCAN', [$cursor, 'MATCH', 'invite:*', 'COUNT', 100]);
            $cursor = (int)$scanResult[0];
            if (!empty($scanResult[1])) {
                foreach ($scanResult[1] as $key) {
                    $keys[] = $key;
                }
            }
        } while ($cursor !== 0);

        if (empty($keys)) {
            return [];
        }

        $now = time();
        $invitations = [];
        foreach ($keys as $key) {
            $data = $redis->executeCommand('HGETALL', [$key]);
            if (empty($data)) continue;

            // HGETALL 返回 [field1, value1, field2, value2, ...]
            $hash = [];
            for ($i = 0; $i < count($data); $i += 2) {
                $hash[$data[$i]] = $data[$i + 1];
            }
            if (!isset($hash['quota'])) continue;

            $code = str_replace('invite:', '', $key);
            $remaining = (int)($hash['remaining'] ?? 0);
            $expiresAt = (int)($hash['expiresAt'] ?? 0);

            $status = 'active';
            if ($expiresAt <= $now) {
                $status = 'expired';
            } elseif ($remaining <= 0) {
                $status = 'used_up';
            }

            $invitations[] = [
                'code' => $code,
                'quota' => (int)($hash['quota'] ?? 0),
                'remaining' => $remaining,
                'expiresAt' => $expiresAt,
                'creatorId' => $hash['creatorId'] ?? '',
                'creatorName' => $hash['creatorName'] ?? '',
                'note' => $hash['note'] ?? '',
                'createdAt' => (int)($hash['createdAt'] ?? 0),
                'status' => $status,
            ];
        }

        // 按创建时间降序
        usort($invitations, function ($a, $b) {
            return $b['createdAt'] - $a['createdAt'];
        });

        return $invitations;
    }

    /**
     * POST /v1/plugin-user/create-invitation
     */
    public function actionCreateInvitation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-invitations');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $quota = $request->getBodyParam('quota');
        $expiresIn = $request->getBodyParam('expiresIn');
        $note = $request->getBodyParam('note', '');

        if (!$quota || !is_numeric($quota) || (int)$quota < 1) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '可注册人数必须为正整数'];
        }
        $quota = (int)$quota;

        $ttl = ($expiresIn && is_numeric($expiresIn) && (int)$expiresIn > 0)
            ? (int)$expiresIn
            : 7 * 24 * 60 * 60; // 默认 7 天

        $code = bin2hex(random_bytes(4)); // 8 字符十六进制
        $expiresAt = time() + $ttl;
        $createdAt = time();
        $key = 'invite:' . $code;

        /** @var User $user */
        $user = $result['user'];
        $redis = Yii::$app->redis;
        $redis->executeCommand('HMSET', [
            $key,
            'quota', (string)$quota,
            'remaining', (string)$quota,
            'expiresAt', (string)$expiresAt,
            'creatorId', (string)$user->id,
            'creatorName', $user->nickname ?? $user->username ?? '',
            'note', $note,
            'createdAt', (string)$createdAt,
        ]);
        $redis->executeCommand('EXPIRE', [$key, $ttl]);

        return [
            'code' => 0,
            'data' => [
                'code' => $code,
                'quota' => $quota,
                'remaining' => $quota,
                'expiresAt' => $expiresAt,
                'creatorId' => $user->id,
                'creatorName' => $user->nickname ?? $user->username ?? '',
                'note' => $note,
                'createdAt' => $createdAt,
            ],
        ];
    }

    /**
     * POST /v1/plugin-user/delete-invitation
     */
    public function actionDeleteInvitation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-invitations');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $code = Yii::$app->request->getBodyParam('code');
        $key = 'invite:' . $code;
        $redis = Yii::$app->redis;

        $exists = $redis->executeCommand('EXISTS', [$key]);
        if (!$exists) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '邀请码不存在或已过期'];
        }

        $redis->executeCommand('DEL', [$key]);
        return ['code' => 0, 'message' => '邀请已撤销'];
    }

    /**
     * GET /v1/plugin-user/check-invitation（公开，无需认证）
     */
    public function actionCheckInvitation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $code = Yii::$app->request->get('code');
        $key = 'invite:' . $code;
        $redis = Yii::$app->redis;

        $data = $redis->executeCommand('HGETALL', [$key]);
        if (empty($data)) {
            return ['valid' => false, 'reason' => '邀请码不存在或已过期'];
        }

        $hash = [];
        for ($i = 0; $i < count($data); $i += 2) {
            $hash[$data[$i]] = $data[$i + 1];
        }

        if (!isset($hash['quota'])) {
            return ['valid' => false, 'reason' => '邀请码不存在或已过期'];
        }

        $now = time();
        $remaining = (int)($hash['remaining'] ?? 0);
        $expiresAt = (int)($hash['expiresAt'] ?? 0);

        if ($expiresAt <= $now) {
            return ['valid' => false, 'reason' => '邀请码已过期'];
        }
        if ($remaining <= 0) {
            return ['valid' => false, 'reason' => '邀请名额已用完'];
        }

        return ['valid' => true, 'remaining' => $remaining, 'expiresAt' => $expiresAt];
    }

    /**
     * GET /v1/plugin-user/invitation-records
     */
    public function actionInvitationRecords()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-invitations');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $code = Yii::$app->request->get('code');
        $rows = (new \yii\db\Query())
            ->select(['ir.id', 'ir.invite_code', 'ir.inviter_id', 'ir.invitee_id', 'ir.created_at', 'u.username', 'u.email'])
            ->from('invitation_record ir')
            ->innerJoin('user u', 'u.id = ir.invitee_id')
            ->where(['ir.invite_code' => $code])
            ->orderBy(['ir.created_at' => SORT_DESC])
            ->all();

        return $rows;
    }

    // ==================== 注册 ====================

    /**
     * POST /v1/plugin-user/register-send-code（公开，无需认证）
     */
    public function actionRegisterSendCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $inviteCode = $request->getBodyParam('inviteCode');
        $email = $request->getBodyParam('email');

        if (empty($inviteCode) || empty($email)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码和邮箱不能为空'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邮箱格式无效'];
        }

        $email = strtolower(trim($email));
        $redis = Yii::$app->redis;

        // 验证邀请码
        $inviteKey = 'invite:' . $inviteCode;
        $inviteData = $redis->executeCommand('HGETALL', [$inviteKey]);
        if (empty($inviteData)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码不存在或已过期'];
        }
        $invite = [];
        for ($i = 0; $i < count($inviteData); $i += 2) {
            $invite[$inviteData[$i]] = $inviteData[$i + 1];
        }
        if (!isset($invite['quota'])) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码不存在或已过期'];
        }
        $now = time();
        if ((int)($invite['expiresAt'] ?? 0) <= $now) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码已过期'];
        }
        if ((int)($invite['remaining'] ?? 0) <= 0) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请名额已用完'];
        }

        // 检查邮箱是否已注册
        $existingEmail = User::findOne(['email' => $email]);
        if ($existingEmail) {
            Yii::$app->response->statusCode = 409;
            return ['code' => 4002, 'message' => '该邮箱已注册'];
        }

        // 速率限制：60 秒
        $rateLimitKey = 'register:rate:' . $email;
        $rateExists = $redis->executeCommand('EXISTS', [$rateLimitKey]);
        if ($rateExists) {
            $ttl = $redis->executeCommand('TTL', [$rateLimitKey]);
            Yii::$app->response->statusCode = 429;
            return ['code' => 4003, 'message' => "发送过于频繁，请 {$ttl} 秒后再试"];
        }

        // 生成 6 位验证码
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeKey = 'register:code:' . $email;
        $codeData = json_encode(['code' => $code, 'created_at' => $now]);
        $redis->executeCommand('SETEX', [$codeKey, 900, $codeData]); // 15 分钟

        // 设置速率限制
        $redis->executeCommand('SETEX', [$rateLimitKey, 60, '1']);

        // 发送邮件
        $emailService = new EmailService();
        $sent = $emailService->sendVerificationCode($email, $code);

        if (!$sent) {
            Yii::$app->response->statusCode = 500;
            return ['code' => 5000, 'message' => '邮件发送失败，请稍后重试'];
        }

        return ['code' => 0, 'message' => '验证码已发送到您的邮箱'];
    }

    /**
     * POST /v1/plugin-user/register（公开，无需认证）
     */
    public function actionRegister()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $inviteCode = $request->getBodyParam('inviteCode');
        $username = $request->getBodyParam('username');
        $password = $request->getBodyParam('password');
        $email = $request->getBodyParam('email');
        $verificationCode = $request->getBodyParam('verificationCode');

        if (empty($inviteCode) || empty($username) || empty($password) || empty($email) || empty($verificationCode)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '所有字段均为必填'];
        }

        $email = strtolower(trim($email));
        $redis = Yii::$app->redis;

        // 验证邀请码
        $inviteKey = 'invite:' . $inviteCode;
        $inviteData = $redis->executeCommand('HGETALL', [$inviteKey]);
        if (empty($inviteData)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码不存在或已过期'];
        }
        $invite = [];
        for ($i = 0; $i < count($inviteData); $i += 2) {
            $invite[$inviteData[$i]] = $inviteData[$i + 1];
        }
        if (!isset($invite['quota'])) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码不存在或已过期'];
        }
        $now = time();
        if ((int)($invite['expiresAt'] ?? 0) <= $now) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请码已过期'];
        }

        // 验证邮箱验证码
        $codeKey = 'register:code:' . $email;
        $codeDataRaw = $redis->executeCommand('GET', [$codeKey]);
        if (!$codeDataRaw) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '验证码已过期或未发送，请重新获取'];
        }
        $parsed = json_decode($codeDataRaw, true);
        if (!$parsed || ($parsed['code'] ?? '') !== $verificationCode) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邮箱验证码不正确'];
        }

        // 用户名唯一性
        if (User::findOne(['username' => $username])) {
            Yii::$app->response->statusCode = 409;
            return ['code' => 4002, 'message' => '用户名已存在'];
        }

        // 邮箱唯一性
        if (User::findOne(['email' => $email])) {
            Yii::$app->response->statusCode = 409;
            return ['code' => 4002, 'message' => '该邮箱已注册'];
        }

        // Redis 原子递减名额
        $newRemaining = $redis->executeCommand('HINCRBY', [$inviteKey, 'remaining', -1]);
        if ((int)$newRemaining < 0) {
            $redis->executeCommand('HINCRBY', [$inviteKey, 'remaining', 1]);
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '邀请名额已用完'];
        }

        // 数据库事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password_hash = Yii::$app->security->generatePasswordHash($password);
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->status = 10;
            $user->created_at = $now;
            $user->updated_at = $now;

            if (!$user->save(false)) {
                throw new \Exception('用户创建失败');
            }

            // 分配默认角色 user
            $authManager = Yii::$app->authManager;
            $roleObj = $authManager->getRole('user');
            if ($roleObj) {
                $authManager->assign($roleObj, $user->id);
            }

            // 写入邀请记录
            Yii::$app->db->createCommand()->insert('invitation_record', [
                'invite_code' => $inviteCode,
                'inviter_id' => (int)($invite['creatorId'] ?? 0),
                'invitee_id' => $user->id,
                'created_at' => $now,
            ])->execute();

            $transaction->commit();

            // 清除验证码
            $redis->executeCommand('DEL', [$codeKey]);

            Yii::$app->response->statusCode = 201;
            return [
                'code' => 0,
                'message' => '注册成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => ['user'],
                ],
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            // 回滚 Redis 名额
            $redis->executeCommand('HINCRBY', [$inviteKey, 'remaining', 1]);
            Yii::$app->response->statusCode = 500;
            return ['code' => 5000, 'message' => '注册失败: ' . $e->getMessage()];
        }
    }
}
