# 删除 Project 表迁移说明

## 📋 迁移文件

**文件名**: `m260121_000001_drop_project_table.php`  
**位置**: `advanced/console/migrations/`

## 🎯 功能说明

这个迁移文件用于安全地删除 `project` 表及其所有相关的外键约束和索引。

## 🔗 依赖关系

### Project 表的外键（需要先删除）

1. **fk-project-user_id**
   - 引用: `user` 表
   - 字段: `user_id`

2. **fk-project-image_id**
   - 引用: `file` 表
   - 字段: `image_id`

### 其他表引用 Project（需要先删除）

1. **logic 表**
   - 外键: `fk-logic-project_id`
   - 字段: `project_id`
   - 引用: `project.id`

## 📊 Project 表结构

根据历史迁移，project 表包含以下字段：

```sql
CREATE TABLE `project` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255),
  `logic` TEXT,
  `configure` TEXT,
  `user_id` INT,
  `sharing` BOOLEAN,
  `image_id` INT,
  `created_at` DATETIME,
  `name` VARCHAR(255),
  `introduce` TEXT,
  `programme_id` INT,
  
  INDEX `idx-project-user_id` (`user_id`),
  INDEX `idx-project-image_id` (`image_id`),
  
  FOREIGN KEY `fk-project-user_id` REFERENCES `user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk-project-image_id` REFERENCES `file`(`id`) ON DELETE CASCADE
);
```

## 🔄 执行顺序

### safeUp() - 删除表

1. 删除其他表中引用 project 的外键
   - `logic.fk-logic-project_id`

2. 删除 project 表自身的外键
   - `fk-project-user_id`
   - `fk-project-image_id`

3. 删除索引
   - `idx-project-user_id`
   - `idx-project-image_id`

4. 删除 project 表

### safeDown() - 恢复表

1. 重新创建 project 表（包含所有字段）
2. 重新创建索引
3. 重新创建外键约束
4. 恢复其他表的外键引用

## ⚠️ 注意事项

1. **数据丢失警告**: 执行此迁移将永久删除 project 表中的所有数据
2. **依赖检查**: 迁移会检查相关表是否存在，避免因表不存在而报错
3. **异常处理**: 使用 try-catch 处理外键和索引可能不存在的情况
4. **可回滚**: safeDown() 方法可以恢复表结构，但无法恢复数据

## 🚀 执行命令

### 应用迁移（删除表）
```bash
cd advanced
php yii migrate
```

### 回滚迁移（恢复表结构）
```bash
cd advanced
php yii migrate/down 1
```

## 📝 相关迁移文件

以下是创建和修改 project 表的历史迁移：

1. `m190501_000000_create_project_table.php` - 创建表
2. `m190527_063623_add_user_id_column_to_project_table.php` - 添加 user_id
3. `m190606_162627_add_introduce_column_to_project_table.php` - 添加 introduce
4. `m190904_051543_add_sharing_column_to_project_table.php` - 添加 sharing
5. `m200312_122903_add_programme_id_column_to_project_table.php` - 添加 programme_id
6. `m210615_183159_add_image_id_column_to_project_table.php` - 添加 image_id
7. `m210615_190039_add_created_at_column_to_project_table.php` - 添加 created_at
8. `m210615_193338_add_name_column_to_project_table.php` - 添加 name

## ✅ 验证

迁移执行后，可以通过以下方式验证：

```bash
# 检查表是否已删除
php yii migrate/history

# 查看数据库中的表
mysql -u root -p -e "SHOW TABLES LIKE 'project';" your_database
```

---

**创建时间**: 2026-01-21  
**状态**: ✅ 已创建，待执行
