<?php

namespace backend\models;

use common\components\IdentityLegacyAdmin;
use Yii;

class IdentityAdminUserSearch extends \yii\base\Model
{
    public $id;
    public $username;
    public $email;
    public $status;
    public string $searchForm = 'User';
    public function formName() { return $this->searchForm; }
    public function rules() { return [[['id', 'status'], 'integer'], [['username', 'email'], 'safe']]; }
    public function search(array $params): IdentityAdminDataProvider
    {
        $this->load($params);
        if (!$this->validate()) return new IdentityAdminDataProvider();
        $payload = array_filter($this->attributes, static fn($value) => $value !== null && $value !== '');
        unset($payload['searchForm']);
        $payload['page'] = max(1, (int)($params['page'] ?? 1));
        $payload['pageSize'] = 20;
        $payload['sort'] = $params['sort'] ?? null;
        $data = IdentityLegacyAdmin::call('user-list', $payload);
        return new IdentityAdminDataProvider(['rows' => array_map(static fn($row) => new IdentityAdminUserView($row), $data['users']),
            'remoteTotal' => (int)$data['total'], 'pagination' => ['pageSize' => 20],
            'sort' => ['attributes' => ['id', 'username', 'email', 'status', 'created_at']]]);
    }
}
