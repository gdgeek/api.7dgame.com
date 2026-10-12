<?php

namespace common\components;

use api\modules\v1\services\IndependentIdentity;
use yii\base\InvalidCallException;
use yii\rbac\Assignment;
use yii\rbac\Role;
use yii\rbac\Permission;

/** Preserve MRPP object rules while sourcing every subject assignment from IAM. */
class IdentityDbManager extends \yii\rbac\DbManager
{
    private array $businessRulePaths = [];
    private ?array $identityGraph = null;
    private ?array $identityAdminSnapshot = null;

    public function checkAccess($userId, $permissionName, $params = [])
    {
        if (!IndependentIdentity::enabled()) {
            return parent::checkAccess($userId, $permissionName, $params);
        }
        if (!$userId || !IndependentIdentity::authority()->allowed((int)$userId, (string)$permissionName)) {
            return false;
        }
        // Identity owns the current global role/permission graph. MRPP still
        // executes its resource rules when a path contains one; a broad IAM
        // allow must never skip ownership, membership, or object visibility.
        return !$this->hasBusinessRule((string)$permissionName)
            || $this->evaluateBusinessRuleAccess($userId, $permissionName, $params);
    }

    protected function evaluateBusinessRuleAccess($userId, $permissionName, $params): bool
    {
        $assignments = $this->getAssignments($userId);
        return $this->evaluateIdentityPath((int)$userId, (string)$permissionName, $params, $assignments, []);
    }

    protected function hasBusinessRule(string $permissionName): bool
    {
        if (array_key_exists($permissionName, $this->businessRulePaths)) {
            return $this->businessRulePaths[$permissionName];
        }
        $pending = [$permissionName];
        $visited = [];
        while ($pending !== []) {
            $name = array_pop($pending);
            if (isset($visited[$name])) {
                continue;
            }
            $visited[$name] = true;
            $item = $this->getItem($name);
            if ($item !== null && $item->ruleName !== null && $item->ruleName !== '') {
                return $this->businessRulePaths[$permissionName] = true;
            }
            $parents = array_column(array_filter($this->graph()['children'], static fn($edge) => $edge['child'] === $name), 'parent');
            foreach ($parents as $parent) {
                $pending[] = $parent;
            }
        }
        return $this->businessRulePaths[$permissionName] = false;
    }

    private function evaluateIdentityPath(int $userId, string $name, array $params, array $assignments, array $visited): bool
    {
        if (isset($visited[$name]) || ($item = $this->getItem($name)) === null) return false;
        $visited[$name] = true;
        if ($item->ruleName !== null && $item->ruleName !== '') {
            $rule = $this->getRule($item->ruleName);
            if ($rule === null) throw new \yii\base\InvalidConfigException('The Identity permission rule is missing.');
            if (!$rule->execute($userId, $item, $params)) return false;
        }
        if (isset($assignments[$name]) || in_array($name, $this->defaultRoles, true)) return true;
        foreach ($this->graph()['children'] as $edge) {
            if ($edge['child'] === $name && $this->evaluateIdentityPath($userId, $edge['parent'], $params, $assignments, $visited)) return true;
        }
        return false;
    }

    private function graph(): array
    {
        if ($this->identityGraph === null) {
            $graph = \Yii::createObject(\api\modules\v1\services\IdentityProviderClient::class)->identityPolicyGraph();
            foreach (['items', 'children', 'rules'] as $field) {
                if (!is_array($graph[$field] ?? null)) throw new \common\components\security\ServiceUnavailableHttpException('Identity returned an invalid policy graph.');
            }
            $this->identityGraph = $graph;
        }
        return $this->identityGraph;
    }

    protected function getItem($name)
    {
        if (!IndependentIdentity::enabled()) return parent::getItem($name);
        foreach ($this->graph()['items'] as $row) {
            if ($row['name'] === $name) return $this->identityItem($row);
        }
        return null;
    }

    protected function getItems($type)
    {
        if (!IndependentIdentity::enabled()) return parent::getItems($type);
        $items = [];
        foreach ($this->graph()['items'] as $row) {
            if ((int)$row['type'] === $type) $items[$row['name']] = $this->identityItem($row);
        }
        return $items;
    }

    private function identityItem(array $row): \yii\rbac\Item
    {
        $class = (int)$row['type'] === 1 ? Role::class : Permission::class;
        return new $class(['name' => $row['name'], 'description' => $row['description'] ?? '',
            'ruleName' => $row['rule_name'] ?? null, 'data' => $this->decodeData($row),
            'createdAt' => isset($row['created_at']) ? (int)$row['created_at'] : null,
            'updatedAt' => isset($row['updated_at']) ? (int)$row['updated_at'] : null]);
    }

    private function decodeData(array $row)
    {
        if (!isset($row['dataBase64'])) return null;
        $data = base64_decode($row['dataBase64'], true);
        if ($data === false) throw new \common\components\security\ServiceUnavailableHttpException('Identity returned invalid serialized policy data.');
        // This is the same trusted PHP metadata stored by Yii DbManager. Only
        // authenticated Identity administrators may mutate this internal graph.
        return unserialize($data);
    }

    public function getChildren($name)
    {
        if (!IndependentIdentity::enabled()) return parent::getChildren($name);
        $children = [];
        foreach ($this->graph()['children'] as $edge) {
            if ($edge['parent'] === $name && ($item = $this->getItem($edge['child'])) !== null) $children[$item->name] = $item;
        }
        return $children;
    }

    public function getRule($name)
    {
        if (!IndependentIdentity::enabled()) return parent::getRule($name);
        foreach ($this->graph()['rules'] as $row) {
            if ($row['name'] === $name) {
                $rule = $this->decodeData($row);
                if (!$rule instanceof \yii\rbac\Rule) throw new \common\components\security\ServiceUnavailableHttpException('Identity returned an invalid permission rule.');
                return $rule;
            }
        }
        return null;
    }

    public function getRules()
    {
        if (!IndependentIdentity::enabled()) return parent::getRules();
        $rules = [];
        foreach ($this->graph()['rules'] as $row) $rules[$row['name']] = $this->getRule($row['name']);
        return $rules;
    }

    private function mutate(string $action, array $payload): bool
    {
        $result = IdentityLegacyAdmin::call('rbac-mutate', array_merge($payload, ['action' => $action]));
        $this->identityGraph = $this->identityAdminSnapshot = null;
        $this->businessRulePaths = [];
        IndependentIdentity::authority()->invalidate();
        return (bool)($result['changed'] ?? $result['success'] ?? false);
    }

    private function itemRow(\yii\rbac\Item $item): array
    {
        return ['name' => $item->name, 'type' => $item->type, 'description' => $item->description,
            'rule_name' => $item->ruleName, 'dataBase64' => $item->data === null ? null : base64_encode(serialize($item->data)),
            'created_at' => $item->createdAt, 'updated_at' => $item->updatedAt];
    }

    protected function addItem($item)
    {
        if (!IndependentIdentity::enabled()) return parent::addItem($item);
        return $this->mutate('add-item', ['item' => $this->itemRow($item)]);
    }
    protected function updateItem($name, $item)
    {
        if (!IndependentIdentity::enabled()) return parent::updateItem($name, $item);
        return $this->mutate('update-item', ['old_name' => $name, 'item' => $this->itemRow($item)]);
    }
    protected function removeItem($item)
    {
        if (!IndependentIdentity::enabled()) return parent::removeItem($item);
        return $this->mutate('remove-item', ['item' => $this->itemRow($item)]);
    }
    protected function addRule($rule)
    {
        if (!IndependentIdentity::enabled()) return parent::addRule($rule);
        return $this->mutate('add-rule', ['rule' => ['name' => $rule->name, 'dataBase64' => base64_encode(serialize($rule)), 'created_at' => $rule->createdAt, 'updated_at' => $rule->updatedAt]]);
    }
    protected function updateRule($name, $rule)
    {
        if (!IndependentIdentity::enabled()) return parent::updateRule($name, $rule);
        return $this->mutate('update-rule', ['old_name' => $name, 'rule' => ['name' => $rule->name, 'dataBase64' => base64_encode(serialize($rule)), 'created_at' => $rule->createdAt, 'updated_at' => $rule->updatedAt]]);
    }
    protected function removeRule($rule)
    {
        if (!IndependentIdentity::enabled()) return parent::removeRule($rule);
        return $this->mutate('remove-rule', ['rule' => ['name' => $rule->name]]);
    }
    public function addChild($parent, $child)
    {
        if (!IndependentIdentity::enabled()) return parent::addChild($parent, $child);
        return $this->mutate('add-child', ['parent' => $parent->name, 'child' => $child->name]);
    }
    public function removeChild($parent, $child)
    {
        if (!IndependentIdentity::enabled()) return parent::removeChild($parent, $child);
        return $this->mutate('remove-child', ['parent' => $parent->name, 'child' => $child->name]);
    }
    public function removeChildren($parent)
    {
        if (!IndependentIdentity::enabled()) return parent::removeChildren($parent);
        return $this->mutate('remove-children', ['parent' => $parent->name]);
    }
    public function hasChild($parent, $child)
    {
        if (!IndependentIdentity::enabled()) return parent::hasChild($parent, $child);
        return isset($this->getChildren($parent->name)[$child->name]);
    }
    public function canAddChild($parent, $child)
    {
        if (!IndependentIdentity::enabled()) return parent::canAddChild($parent, $child);
        if ($parent->name === $child->name || ($parent instanceof Permission && $child instanceof Role)) return false;
        $pending = [$child->name]; $seen = [];
        while ($pending !== []) {
            $name = array_pop($pending);
            if ($name === $parent->name) return false;
            if (isset($seen[$name])) continue;
            $seen[$name] = true;
            foreach ($this->getChildren($name) as $item) $pending[] = $item->name;
        }
        return true;
    }
    public function getChildRoles($roleName)
    {
        if (!IndependentIdentity::enabled()) return parent::getChildRoles($roleName);
        $role = $this->getRole($roleName);
        if ($role === null) throw new \yii\base\InvalidParamException('Role does not exist.');
        return $this->descendants($roleName, Role::class) + [$roleName => $role];
    }
    public function getPermissionsByRole($roleName)
    {
        if (!IndependentIdentity::enabled()) return parent::getPermissionsByRole($roleName);
        return $this->descendants($roleName, Permission::class);
    }
    private function descendants(string $name, string $class): array
    {
        $pending = [$name]; $seen = []; $result = [];
        while ($pending !== []) {
            $name = array_pop($pending);
            if (isset($seen[$name])) continue;
            $seen[$name] = true;
            foreach ($this->getChildren($name) as $item) {
                if ($item instanceof $class) $result[$item->name] = $item;
                $pending[] = $item->name;
            }
        }
        return $result;
    }
    public function getUserIdsByRole($roleName)
    {
        if (!IndependentIdentity::enabled()) return parent::getUserIdsByRole($roleName);
        $this->identityAdminSnapshot ??= IdentityLegacyAdmin::call('rbac-snapshot');
        return array_values(array_unique(array_column(array_filter($this->identityAdminSnapshot['assignments'], static fn($row) => $row['item_name'] === $roleName), 'user_id')));
    }

    public function getAssignment($roleName, $userId)
    {
        if (IndependentIdentity::enabled()) {
            return $this->getAssignments($userId)[$roleName] ?? null;
        }
        return parent::getAssignment($roleName, $userId);
    }

    public function getPermissionsByUser($userId)
    {
        if (!IndependentIdentity::enabled()) {
            return parent::getPermissionsByUser($userId);
        }
        $subject = IndependentIdentity::authority()->subject((int)$userId);
        $permissions = [];
        if ($subject['status'] === 10) {
            foreach ($subject['permissions'] as $name) {
                $permissions[$name] = new Permission(['name' => $name]);
            }
        }
        return $permissions;
    }

    public function getAssignments($userId)
    {
        if (!IndependentIdentity::enabled()) {
            return parent::getAssignments($userId);
        }
        if (!$userId) {
            return [];
        }
        if (isset(\Yii::$app->controller) && str_starts_with(\Yii::$app->controller->uniqueId, 'admin/')) {
            $this->identityAdminSnapshot ??= IdentityLegacyAdmin::call('rbac-snapshot');
            $assignments = [];
            foreach ($this->identityAdminSnapshot['assignments'] as $row) {
                if ((string)$row['user_id'] === (string)$userId) $assignments[$row['item_name']] = new Assignment([
                    'roleName' => $row['item_name'], 'userId' => (string)$userId, 'createdAt' => (int)$row['created_at'],
                ]);
            }
            return $assignments;
        }
        $subject = IndependentIdentity::authority()->subject((int)$userId);
        if ($subject['status'] !== 10) {
            return [];
        }
        $assignments = [];
        // Effective inherited permissions are deliberately not flattened into
        // assignments: intermediate MRPP object rules must still execute.
        foreach (array_unique(array_merge($subject['roles'], $subject['directPermissions'])) as $name) {
            $assignments[$name] = new Assignment(['userId' => (string)$userId, 'roleName' => $name, 'createdAt' => 0]);
        }

        return $assignments;
    }

    public function getRolesByUser($userId)
    {
        if (!IndependentIdentity::enabled()) {
            return parent::getRolesByUser($userId);
        }
        if (!$userId) {
            return [];
        }
        $roles = [];
        foreach (IndependentIdentity::authority()->roles((int)$userId) as $name) {
            $roles[$name] = new Role(['name' => $name]);
        }

        return $roles;
    }

    public function assign($role, $userId)
    {
        if (IndependentIdentity::enabled()) {
            $this->mutate('assign', ['role_name' => $role->name, 'user_id' => (string)$userId]);
            return new Assignment(['roleName' => $role->name, 'userId' => (string)$userId, 'createdAt' => time()]);
        }
        return parent::assign($role, $userId);
    }

    public function revoke($role, $userId)
    {
        if (IndependentIdentity::enabled()) return $this->mutate('revoke', ['role_name' => $role->name, 'user_id' => (string)$userId]);
        return parent::revoke($role, $userId);
    }

    public function revokeAll($userId)
    {
        if (IndependentIdentity::enabled()) return $this->mutate('revoke-all', ['user_id' => (string)$userId]);
        return parent::revokeAll($userId);
    }

    public function removeAll()
    {
        $this->assertBulkRemovalAllowed();
        return parent::removeAll();
    }

    public function removeAllPermissions() { $this->assertBulkRemovalAllowed(); return parent::removeAllPermissions(); }
    public function removeAllRoles() { $this->assertBulkRemovalAllowed(); return parent::removeAllRoles(); }
    public function removeAllRules() { $this->assertBulkRemovalAllowed(); return parent::removeAllRules(); }
    public function removeAllAssignments() { $this->assertBulkRemovalAllowed(); return parent::removeAllAssignments(); }

    private function assertBulkRemovalAllowed(): void
    {
        if (IndependentIdentity::enabled()) throw new InvalidCallException('Bulk removal of Identity policy is not supported by this business service.');
    }
}
