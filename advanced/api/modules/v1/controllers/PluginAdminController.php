<?php

namespace api\modules\v1\controllers;

use common\models\PluginPermissionConfig;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use Yii;
use yii\web\Response;

/**
 * PluginAdminController 提供系统管理插件的所有 API
 *
 * 与 PluginUserController 完全独立，自行实现 JWT 认证。
 * 路由前缀：/v1/plugin-admin/
 */
class PluginAdminController extends \yii\rest\Controller
{
    private const PLUGIN_NAME = 'system-admin';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
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
            // 权限配置 CRUD
            'permissions'        => ['GET'],
            'create-permission'  => ['POST'],
            'update-permission'  => ['PUT'],
            'delete-permission'  => ['POST'],
            // 插件注册 CRUD
            'plugins'            => ['GET'],
            'create-plugin'      => ['POST'],
            'update-plugin'      => ['PUT'],
            'delete-plugin'      => ['POST'],
            // 菜单分组 CRUD
            'menu-groups'        => ['GET'],
            'create-menu-group'  => ['POST'],
            'update-menu-group'  => ['PUT'],
            'delete-menu-group'  => ['POST'],
        ];
    }

    /**
     * 合并认证 + 权限检查
     * 参照 PluginUserController::resolveUserWithPermission() 模式
     *
     * @param string $action 操作标识（如 'manage-permissions'、'manage-plugins'）
     * @return array 成功时返回 ['user' => ..., 'roles' => [...]]，失败时返回 ['error' => [...]]
     */
    protected function resolveUserWithPermission(string $action): array
    {
        $user = Yii::$app->user->identity;
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));

        $allowed = PluginPermissionConfig::checkPermission($roles, self::PLUGIN_NAME, $action);
        if (!$allowed) {
            Yii::$app->response->statusCode = 403;
            return ['error' => ['code' => 2003, 'message' => '没有权限执行此操作']];
        }

        return ['user' => $user, 'roles' => $roles];
    }

    // ==================== 权限配置 CRUD ====================

    /**
     * GET /v1/plugin-admin/permissions
     */
    public function actionPermissions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-permissions');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $roleOrPermission = $request->get('role_or_permission');
        $pluginName       = $request->get('plugin_name');
        $action           = $request->get('action');
        $page             = (int) $request->get('page', 1);
        $perPage          = (int) $request->get('per_page', 20);

        $query = PluginPermissionConfig::find();

        if ($roleOrPermission !== null && $roleOrPermission !== '') {
            $query->andWhere(['like', 'role_or_permission', $roleOrPermission]);
        }
        if ($pluginName !== null && $pluginName !== '') {
            $query->andWhere(['like', 'plugin_name', $pluginName]);
        }
        if ($action !== null && $action !== '') {
            $query->andWhere(['like', 'action', $action]);
        }

        $total  = $query->count();
        $offset = ($page - 1) * $perPage;
        $models = $query->offset($offset)->limit($perPage)->all();

        $items = array_map(function ($m) {
            return [
                'id'                => $m->id,
                'role_or_permission' => $m->role_or_permission,
                'plugin_name'       => $m->plugin_name,
                'action'            => $m->action,
                'created_at'        => $m->created_at,
                'updated_at'        => $m->updated_at,
            ];
        }, $models);

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [
                'items'    => $items,
                'total'    => (int) $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * POST /v1/plugin-admin/create-permission
     */
    public function actionCreatePermission()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-permissions');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request          = Yii::$app->request;
        $roleOrPermission = $request->getBodyParam('role_or_permission');
        $pluginName       = $request->getBodyParam('plugin_name');
        $action           = $request->getBodyParam('action');

        if (empty($roleOrPermission) || empty($pluginName) || empty($action)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: role_or_permission, plugin_name, action'];
        }

        if (mb_strlen($roleOrPermission) > 64) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'role_or_permission 长度不能超过 64'];
        }
        if (mb_strlen($pluginName) > 128) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'plugin_name 长度不能超过 128'];
        }
        if (mb_strlen($action) > 128) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'action 长度不能超过 128'];
        }

        $model                    = new PluginPermissionConfig();
        $model->role_or_permission = $roleOrPermission;
        $model->plugin_name       = $pluginName;
        $model->action            = $action;

        try {
            if (!$model->save()) {
                Yii::$app->response->statusCode = 400;
                return ['code' => 4001, 'message' => implode('; ', $model->getFirstErrors())];
            }
        } catch (\yii\db\IntegrityException $e) {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4002, 'message' => '唯一键冲突'];
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [
                'id'                => $model->id,
                'role_or_permission' => $model->role_or_permission,
                'plugin_name'       => $model->plugin_name,
                'action'            => $model->action,
                'created_at'        => $model->created_at,
            ],
        ];
    }

    /**
     * PUT /v1/plugin-admin/update-permission
     */
    public function actionUpdatePermission()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-permissions');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $id      = $request->getBodyParam('id');

        $model = PluginPermissionConfig::findOne($id);
        if ($model === null) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $roleOrPermission = $request->getBodyParam('role_or_permission');
        $pluginName       = $request->getBodyParam('plugin_name');
        $action           = $request->getBodyParam('action');

        if ($roleOrPermission !== null) {
            $model->role_or_permission = $roleOrPermission;
        }
        if ($pluginName !== null) {
            $model->plugin_name = $pluginName;
        }
        if ($action !== null) {
            $model->action = $action;
        }

        if (!$model->save()) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => implode('; ', $model->getFirstErrors())];
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [
                'id'                => $model->id,
                'role_or_permission' => $model->role_or_permission,
                'plugin_name'       => $model->plugin_name,
                'action'            => $model->action,
                'updated_at'        => $model->updated_at,
            ],
        ];
    }

    /**
     * POST /v1/plugin-admin/delete-permission
     */
    public function actionDeletePermission()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-permissions');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $id    = Yii::$app->request->getBodyParam('id');
        $model = PluginPermissionConfig::findOne($id);
        if ($model === null) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $model->delete();

        return ['code' => 0, 'message' => 'ok'];
    }

    // ==================== 插件注册 CRUD ====================

    /**
     * GET /v1/plugin-admin/plugins
     */
    public function actionPlugins()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $domain  = $request->get('domain');
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 20);

        $query = (new \yii\db\Query())->from('plugins');

        if ($domain !== null && $domain !== '') {
            $query->andWhere(['domain' => $domain]);
        }

        $pluginDb = Yii::$app->get('pluginDb');
        $total    = $query->count('*', $pluginDb);
        $offset   = ($page - 1) * $perPage;
        $items    = $query->offset($offset)->limit($perPage)->all($pluginDb);

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [
                'items'    => $items,
                'total'    => (int) $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * POST /v1/plugin-admin/create-plugin
     */
    public function actionCreatePlugin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $id      = $request->getBodyParam('id');
        $name    = $request->getBodyParam('name');
        $url     = $request->getBodyParam('url');

        // Validate required fields
        if (empty($id)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: id'];
        }
        if (mb_strlen($id) > 64) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'id 长度不能超过 64'];
        }
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $id)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'id 只能包含字母、数字和连字符'];
        }
        if (empty($name)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: name'];
        }
        if (mb_strlen($name) > 128) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'name 长度不能超过 128'];
        }
        if (empty($url)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: url'];
        }
        if (mb_strlen($url) > 512) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'url 长度不能超过 512'];
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'url 格式不合法'];
        }

        $data = [
            'id'             => $id,
            'name'           => $name,
            'url'            => $url,
            'name_i18n'      => $request->getBodyParam('name_i18n'),
            'description'    => $request->getBodyParam('description'),
            'icon'           => $request->getBodyParam('icon'),
            'group_id'       => $request->getBodyParam('group_id'),
            'enabled'        => $request->getBodyParam('enabled', 1),
            'order'          => $request->getBodyParam('order', 0),
            'allowed_origin' => $request->getBodyParam('allowed_origin'),
            'version'        => $request->getBodyParam('version'),
            'domain'         => $request->getBodyParam('domain'),
        ];

        // Encode name_i18n to JSON string if it's an array
        if (is_array($data['name_i18n'])) {
            $data['name_i18n'] = json_encode($data['name_i18n'], JSON_UNESCAPED_UNICODE);
        }

        try {
            Yii::$app->get('pluginDb')->createCommand()->insert('plugins', $data)->execute();
        } catch (\yii\db\IntegrityException $e) {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4002, 'message' => '主键冲突'];
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => $data,
        ];
    }

    /**
     * PUT /v1/plugin-admin/update-plugin
     */
    public function actionUpdatePlugin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request  = Yii::$app->request;
        $id       = $request->getBodyParam('id');
        $pluginDb = Yii::$app->get('pluginDb');

        $record = (new \yii\db\Query())->from('plugins')->where(['id' => $id])->one($pluginDb);
        if (!$record) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $data = [];
        $fields = ['name', 'name_i18n', 'description', 'url', 'icon', 'group_id', 'enabled', 'order', 'allowed_origin', 'version', 'domain'];
        foreach ($fields as $field) {
            $value = $request->getBodyParam($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        // Encode name_i18n to JSON string if it's an array
        if (isset($data['name_i18n']) && is_array($data['name_i18n'])) {
            $data['name_i18n'] = json_encode($data['name_i18n'], JSON_UNESCAPED_UNICODE);
        }

        if (!empty($data)) {
            $pluginDb->createCommand()->update('plugins', $data, ['id' => $id])->execute();
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => array_merge(['id' => $id], $data),
        ];
    }

    /**
     * POST /v1/plugin-admin/delete-plugin
     */
    public function actionDeletePlugin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $id       = Yii::$app->request->getBodyParam('id');
        $pluginDb = Yii::$app->get('pluginDb');

        $record = (new \yii\db\Query())->from('plugins')->where(['id' => $id])->one($pluginDb);
        if (!$record) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $pluginDb->createCommand()->delete('plugins', ['id' => $id])->execute();

        return ['code' => 0, 'message' => 'ok'];
    }

    // ==================== 菜单分组 CRUD ====================

    /**
     * GET /v1/plugin-admin/menu-groups
     */
    public function actionMenuGroups()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $items = (new \yii\db\Query())
            ->from('plugin_menu_groups')
            ->orderBy(['order' => SORT_ASC])
            ->all(Yii::$app->get('pluginDb'));

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => ['items' => $items],
        ];
    }

    /**
     * POST /v1/plugin-admin/create-menu-group
     */
    public function actionCreateMenuGroup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request = Yii::$app->request;
        $id      = $request->getBodyParam('id');
        $name    = $request->getBodyParam('name');

        if (empty($id)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: id'];
        }
        if (mb_strlen($id) > 64) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'id 长度不能超过 64'];
        }
        if (empty($name)) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => '缺少必要参数: name'];
        }
        if (mb_strlen($name) > 128) {
            Yii::$app->response->statusCode = 400;
            return ['code' => 4001, 'message' => 'name 长度不能超过 128'];
        }

        $nameI18n = $request->getBodyParam('name_i18n');
        if (is_array($nameI18n)) {
            $nameI18n = json_encode($nameI18n, JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'id'       => $id,
            'name'     => $name,
            'name_i18n' => $nameI18n,
            'icon'     => $request->getBodyParam('icon'),
            'order'    => $request->getBodyParam('order', 0),
            'domain'   => $request->getBodyParam('domain'),
        ];

        try {
            Yii::$app->get('pluginDb')->createCommand()->insert('plugin_menu_groups', $data)->execute();
        } catch (\yii\db\IntegrityException $e) {
            Yii::$app->response->statusCode = 422;
            return ['code' => 4002, 'message' => '主键冲突'];
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => $data,
        ];
    }

    /**
     * PUT /v1/plugin-admin/update-menu-group
     */
    public function actionUpdateMenuGroup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $request  = Yii::$app->request;
        $id       = $request->getBodyParam('id');
        $pluginDb = Yii::$app->get('pluginDb');

        $record = (new \yii\db\Query())->from('plugin_menu_groups')->where(['id' => $id])->one($pluginDb);
        if (!$record) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $data = [];
        foreach (['name', 'name_i18n', 'icon', 'order', 'domain'] as $field) {
            $value = $request->getBodyParam($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        if (isset($data['name_i18n']) && is_array($data['name_i18n'])) {
            $data['name_i18n'] = json_encode($data['name_i18n'], JSON_UNESCAPED_UNICODE);
        }

        if (!empty($data)) {
            $pluginDb->createCommand()->update('plugin_menu_groups', $data, ['id' => $id])->execute();
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => array_merge(['id' => $id], $data),
        ];
    }

    /**
     * POST /v1/plugin-admin/delete-menu-group
     */
    public function actionDeleteMenuGroup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = $this->resolveUserWithPermission('manage-plugins');
        if (isset($result['error'])) {
            return $result['error'];
        }

        $id       = Yii::$app->request->getBodyParam('id');
        $pluginDb = Yii::$app->get('pluginDb');

        $record = (new \yii\db\Query())->from('plugin_menu_groups')->where(['id' => $id])->one($pluginDb);
        if (!$record) {
            Yii::$app->response->statusCode = 404;
            return ['code' => 4004, 'message' => '记录不存在'];
        }

        $pluginDb->createCommand()->delete('plugin_menu_groups', ['id' => $id])->execute();

        return ['code' => 0, 'message' => 'ok'];
    }
}
