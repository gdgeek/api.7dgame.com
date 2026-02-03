<?php
/**
 * 覆盖 mdm\admin\models\searchs\AuthItem 以修复 PHP 8.1+ 兼容性问题
 * 原始文件: vendor/mdmsoft/yii2-admin/models/searchs/AuthItem.php
 */

namespace mdm\admin\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use mdm\admin\components\Configs;
use yii\rbac\Item;

class AuthItem extends Model
{
    const TYPE_ROUTE = 101;

    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;

    public function rules()
    {
        return [
            [['name', 'ruleName', 'description'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
            'item_name' => Yii::t('rbac-admin', 'Name'),
            'type' => Yii::t('rbac-admin', 'Type'),
            'description' => Yii::t('rbac-admin', 'Description'),
            'ruleName' => Yii::t('rbac-admin', 'Rule Name'),
            'data' => Yii::t('rbac-admin', 'Data'),
        ];
    }

    public function search($params)
    {
        $authManager = Configs::authManager();
        $advanced = Configs::instance()->advanced;
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = array_filter($authManager->getPermissions(), function($item) use ($advanced){
              $isPermission = $this->type == Item::TYPE_PERMISSION;
              if ($advanced) {
                return $isPermission xor (strncmp($item->name, '/', 1) === 0 or strncmp($item->name, '@', 1) === 0);
              }
              else {
                return $isPermission xor strncmp($item->name, '/', 1) === 0;
              }
            });
        }
        $this->load($params);
        if ($this->validate()) {
            // 修复 PHP 8.1+ 兼容性：使用 ?? '' 避免 null 传递给 trim()
            $search = mb_strtolower(trim($this->name ?? ''));
            $desc = mb_strtolower(trim($this->description ?? ''));
            $ruleName = $this->ruleName;
            foreach ($items as $name => $item) {
                $f = (empty($search) || mb_strpos(mb_strtolower($item->name), $search) !== false) &&
                    (empty($desc) || mb_strpos(mb_strtolower($item->description ?? ''), $desc) !== false) &&
                    (empty($ruleName) || $item->ruleName == $ruleName);
                if (!$f) {
                    unset($items[$name]);
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }
}
