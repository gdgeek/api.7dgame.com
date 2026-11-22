# 本地 dev 的字幕素材

启用 `ENABLE_DEV_SUBTITLE_RESOURCES=1` 后，持有 `admin` 或 `root` 角色的有效人员账号可上传、预览、编辑自己的字幕，并管理自己音视频素材的字幕绑定。其他已登录账号访问字幕管理接口或申请字幕上传凭据时收到 HTTP 403。主站依据开发域名的 `features.subtitle` 和账号角色显示入口。音视频关联使用迁移 `m261118_000002_create_resource_subtitle`。

| 请求 | 请求参数 | 响应 |
| --- | --- | --- |
| GET /v1/subtitles | 可选 `search` 按名称包含关键词筛选。可选 `page` 从 1 开始，每页 20 项 | 本人匹配的素材摘要 `items` 和匹配总数 `total` |
| POST /v1/subtitles/prepare | `srt` | 校验并返回 JSON 文本和公共素材桶中当前用户的字幕上传目录 |
| POST /v1/subtitles | `name`、`uploadedFileId`、`srt`（UTF-8 正文，最多 1 MiB 和 10,000 条） | 201，返回新资源、文件版本及字幕条目 |
| GET /v1/subtitles/{id} | 当前用户的字幕素材 ID | 元数据和 `cues`，每条含 `startMs`、`endMs`、`text` |
| PUT /v1/subtitles/{id} | `name`、`srt` 和打开编辑时的 `fileId` 以及新文件的 `uploadedFileId` | 200，返回新版本。版本过期返回 409，字幕格式错误返回 422 |
| GET /v1/resources/{id}/subtitle | 本人的音频或视频素材 ID | `subtitle` 为当前字幕的 `{id,name}`，未绑定时为 `null` |
| POST /v1/resources/{id}/subtitle | `name`、`srt`、`uploadedFileId`、`previousSubtitleResourceId` | 201，在同一事务创建字幕素材并绑定。格式错误返回 422，绑定冲突返回 409，原绑定保留 |
| PUT /v1/resources/{id}/subtitle | `subtitleResourceId` 为本人的字幕素材 ID，解除时传 `null`。同时传打开时的 `previousSubtitleResourceId` | 返回保存结果。重复保存同一绑定成功，其他窗口已更改绑定时返回 409 |

标准上传流程对 JSON 内容计算 MD5，检查目标对象，按需上传，再通过 `POST /v1/files` 登记文件。字幕与音视频共用公共素材桶。字幕对象路径为 `subtitles/{userId}/{md5}.json`。同一用户重复上传相同内容时复用对象和文件记录。修改正文或时间码产生新文件。SRT 导出取当前已保存的字幕条目。字幕素材的 `resource.type` 为 `subtitle`，`file_id` 指向当前文件，`info` 只保存 `cueCount` 和 `durationMs`。更新保留素材 ID 和旧文件版本。

音视频与字幕的关联单独保存在 `resource_subtitle`。主键 `media_resource_id` 限定每个音视频最多绑定一份字幕，带索引的 `subtitle_resource_id` 允许多个音视频复用同一份字幕。两列都引用 `resource.id`，删除任一端时级联清理对应关联。解除绑定只删除一行关联。字幕换文件版本时原绑定继续指向同一份字幕素材。

素材列表、素材详情及实体的 `resources` 返回音视频素材的布尔字段 `hasSubtitle`。编辑器用这个字段显示节点字幕设置。只有存在同一所有者的有效字幕绑定并启用字幕功能时，值才为 `true`。绑定管理继续由上表中的字幕接口提供。

## 场景快照

生成快照时，已绑定字幕的音视频在原有 `info` JSON 字符串中增加整数 `subtitleResourceId`。该 ID 对应同一份 `resources[]` 中的 `type: "subtitle"` 素材。共用一份字幕的音视频各自携带引用，资源列表只收录一次字幕。

Three.js 编辑器将音频和视频的节点设置写入 `parameters.subtitleStyle`。可选字段 `fontSize` 为 12–96 的 JSON 数字，默认 36。可选字段 `color` 为 `#RRGGBB`，默认 `#FFFFFF`。`enabled: false` 请求隐藏当前节点的字幕，省略时请求显示。恢复默认会删除对应字段。节点样式、开关与媒体字幕绑定可独立保存，同一媒体的多个节点可各自设置。

字幕的 `file.key` 为 JSON 内容的 `{md5}.json`，`file.id` 指向发布时的文件版本。公共 COS 字幕的 `file.url` 是可匿名 GET 的 HTTPS 对象地址。本地磁盘模式使用公共目录 `/storage/store/subtitles/{userId}/{md5}.json`。两种地址都不需要下载签名。取得公共文件地址的人可读取字幕正文，包括从私有场景引用的公共字幕。编辑字幕或变更绑定后，重新生成快照才会反映变更。

历史私有文件继续通过 `GET /v1/verses/{verseId}/snapshot/subtitles/{fileId}` 下载。该入口校验场景查看权限和当前快照的文件版本，对旧的私有 COS 文件签发 5 分钟 GET 地址，对旧的本地私有文件返回 JSON。重新保存字幕时，编辑器将该版本上传到公共素材桶。
