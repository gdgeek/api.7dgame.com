# 邮箱状态不一致与流程阻塞问题报告

更新时间：2026-02-25

## 1. 问题背景

同一账号联调时，后端返回了互相矛盾的邮箱状态，导致前端无法正确渲染和执行“解绑/改绑”流程。

## 2. 原始问题现象（实测）

账号：`dirui`

原始接口返回（修复前）：

1. `GET /v1/user/info`
   - `email = null`
   - `emailBind = null`
2. `GET /v1/email/status`
   - `email = "dirui1981@gmail.com"`
   - `email_verified = false`
3. `POST /v1/email/unbind`
   - 拒绝：`"当前邮箱未验证，无法解绑"`
4. `POST /v1/email/send-change-confirmation`
   - 拒绝：`"当前账号未绑定已验证邮箱"`

结论：

- `user/info` 与 `email/status` 的邮箱状态不一致。
- 当邮箱“已绑定但未验证”时，既不能解绑，也不能进入改绑二次确认，流程形成死锁。

## 3. 根因分析

1. `user/info` 未返回邮箱状态字段  
`UserController::getUserData()` 只返回 `userData/userInfo/roles`，没有统一输出邮箱状态。

2. 解绑规则过严  
`unbind` 强制要求“当前邮箱已验证 + 验证码”，导致“已绑定未验证邮箱”用户无法解绑。

3. 二次确认接口提示不清晰  
未绑定与未验证场景使用了不够明确的错误语义，前端无法区分下一步动作。

## 4. 已完成修复

### 4.1 对齐 `user/info` 邮箱状态输出

文件：`advanced/api/modules/v1/controllers/UserController.php`

新增返回字段：

- `email`
- `emailVerified`
- `emailVerifiedAt`
- `emailBind`（对象，含 `email/verified/verifiedAt/verifiedAtFormatted`）

### 4.2 调整 `unbind` 规则，消除流程死锁

文件：

- `advanced/api/modules/v1/controllers/EmailController.php`
- `advanced/api/modules/v1/services/EmailVerificationService.php`

新规则：

1. 若当前邮箱已验证：解绑需要验证码；
2. 若当前邮箱未验证：允许直接解绑（无需验证码）；
3. 若未绑定邮箱：返回明确错误。

### 4.3 优化二次确认错误语义

文件：`advanced/api/modules/v1/services/EmailVerificationService.php`

`send-change-confirmation` 现在区分：

- 未绑定：`当前账号未绑定邮箱`
- 已绑定但未验证：`当前邮箱未验证，无需二次确认，可直接改绑`

## 5. 修复后实测结果（同账号）

基于 `http://localhost:81` 联调结果：

1. `GET /v1/user/info`
   - 返回 `email = "dirui1981@gmail.com"`
   - 返回 `emailVerified = false`
   - 返回 `emailBind` 对象（与 `email/status` 一致）
2. `GET /v1/email/status`
   - 返回 `email = "dirui1981@gmail.com"`
   - 返回 `email_verified = false`
3. `POST /v1/email/unbind`（无验证码）
   - 成功：`邮箱解绑成功`
4. 解绑后 `GET /v1/email/status`
   - 返回 `email = null`
5. 解绑后 `POST /v1/email/send-change-confirmation`
   - 返回：`当前账号未绑定邮箱`

结论：状态已一致，流程阻塞已解除。

## 6. 当前流程规则（修复后）

1. 首次绑定：发送新邮箱验证码 -> 提交验证码绑定。
2. 改绑（旧邮箱已验证）：先做旧邮箱二次确认，再绑定新邮箱。
3. 改绑（旧邮箱未验证）：无需旧邮箱二次确认，可直接绑定新邮箱。
4. 解绑：
   - 已验证邮箱：需验证码；
   - 未验证邮箱：可直接解绑。

## 7. 端口与环境说明

文档默认示例端口是 `8081`，但当前本机容器实测映射为：

- API: `http://localhost:81`

请前端联调时以 `docker ps` 实际端口映射为准。

## 8. 相关文档

- 前端对接文档：`docs/EMAIL_API_FOR_FRONTEND.md`
