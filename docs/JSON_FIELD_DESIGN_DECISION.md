# JSON 字段处理设计决策

## 🎯 最终方案：手动处理（当前实现）

### 实现方式

在需要使用 JSON 数据的方法中，手动检查类型并解码：

```php
public function getResourceIds()
{
    $data = $this->data;
    // 如果 data 是 JSON 字符串，解码为数组
    if (is_string($data)) {
        $data = json_decode($data, true);
    }
    return \api\modules\v1\helper\Meta2Resources::Handle($data);
}
```

### ✅ 优点

1. **安全性高**: 不修改原始属性值，避免意外副作用
2. **兼容性好**: 
   - 兼容 MySQL JSON 类型（PDO 可能返回数组）
   - 兼容 TEXT 类型（返回字符串）
   - 兼容手动赋值的数组
3. **可预测**: 属性值保持数据库返回的原始状态
4. **无状态问题**: 不会出现保存后状态不一致的问题

### ❌ 缺点

1. 需要在每个使用 JSON 数据的地方手动处理
2. 代码略显冗余

## 🚫 被拒绝的方案

### 方案 A: afterFind/beforeSave 自动转换

```php
public function afterFind()
{
    // 解码 JSON 字符串为数组
    if (is_string($this->data)) {
        $this->data = json_decode($this->data, true);
    }
}

public function beforeSave($insert)
{
    // 编码数组为 JSON 字符串
    if (is_array($this->data)) {
        $this->data = json_encode($this->data);
    }
    return parent::beforeSave($insert);
}
```

#### 问题

1. **保存后状态不一致**:
   ```php
   $meta->data = ['type' => 'test'];  // 数组
   $meta->save();
   // beforeSave 将 data 转为字符串
   // 保存后 $meta->data 是字符串，不是数组！
   var_dump($meta->data);  // string(17) "{"type":"test"}"
   ```

2. **对象处理问题**:
   ```php
   $meta->data = (object)['type' => 'test'];  // stdClass 对象
   $meta->save();
   // beforeSave 将对象 json_encode 为字符串
   // 但对象本身没有被转换，导致不一致
   ```

### 方案 B: afterFind/beforeSave + afterSave 恢复

```php
private $_jsonFieldsBackup = [];

public function beforeSave($insert)
{
    // 备份原始值
    if (is_array($this->data)) {
        $this->_jsonFieldsBackup['data'] = $this->data;
        $this->data = json_encode($this->data);
    }
    return parent::beforeSave($insert);
}

public function afterSave($insert, $changedAttributes)
{
    // 恢复原始值
    foreach ($this->_jsonFieldsBackup as $field => $value) {
        $this->$field = $value;
    }
    parent::afterSave($insert, $changedAttributes);
}
```

#### 问题

1. **对象类型不一致**:
   ```php
   $meta->data = (object)['type' => 'test'];  // stdClass
   $meta->save();
   // beforeSave: 备份对象，编码为字符串
   // afterSave: 恢复对象
   // 但如果有人期望是数组，就会出问题
   ```

2. **复杂性增加**: 需要维护备份状态，容易出错

3. **性能开销**: 每次保存都需要备份和恢复

## 📋 当前实现的最佳实践

### 1. 在 getter 方法中处理

```php
public function getResourceIds()
{
    $data = $this->data;
    if (is_string($data)) {
        $data = json_decode($data, true);
    }
    return Helper::process($data);
}
```

### 2. 在 setter 方法中验证（可选）

```php
public function setData($value)
{
    if (is_string($value)) {
        // 验证是否是有效的 JSON
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON');
        }
    }
    $this->_attributes['data'] = $value;
}
```

### 3. 在业务逻辑中明确处理

```php
// 读取
$meta = Meta::findOne($id);
$data = is_string($meta->data) ? json_decode($meta->data, true) : $meta->data;

// 写入
$meta->data = json_encode(['type' => 'test']);
$meta->save();
```

## 🔍 MySQL JSON 类型行为

### PDO 驱动差异

不同的 MySQL 驱动和版本对 JSON 类型的处理不同：

1. **mysqlnd (MySQL Native Driver)**:
   - 可能自动将 JSON 列解码为 PHP 数组
   - 取决于 PDO 配置和 MySQL 版本

2. **libmysqlclient**:
   - 通常返回 JSON 字符串
   - 需要手动解码

### 我们的兼容方案

```php
// 兼容两种情况
$data = $this->data;
if (is_string($data)) {
    $data = json_decode($data, true);
}
// 现在 $data 一定是数组（或 null）
```

## 📝 总结

**当前的手动处理方案是最佳选择**，因为：

1. ✅ 安全可靠，无副作用
2. ✅ 兼容性好，适应不同环境
3. ✅ 可预测，行为明确
4. ✅ 易于调试和维护

虽然需要在多处手动处理，但这是为了安全性和可靠性做出的合理权衡。

## 🔗 相关文档

- [系统升级 API](./SYSTEM_UPGRADE_API.md)
- [Meta 模型](../advanced/api/modules/v1/models/Meta.php)
- [Verse 模型](../advanced/api/modules/v1/models/Verse.php)
