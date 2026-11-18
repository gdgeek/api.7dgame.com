<?php

namespace api\modules\v1\services;

use common\components\UuidHelper;
use yii\db\Connection;
use yii\db\Query;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

final class SubtitleResources
{
    public const FILE_PREFIX = 'subtitles/';
    private const TIMESTAMP = '(\d{2,3}):([0-5]\d):([0-5]\d)[,.](\d{3})';

    public function __construct(private Connection $db, private int $owner)
    {
    }

    public static function enabled(): bool
    {
        return getenv('ENABLE_DEV_SUBTITLE_RESOURCES') === '1' && in_array(getenv('FILE_STORAGE_DRIVER') ?: 'cos', ['local', 'cos'], true);
    }

    private static function milliseconds(array $parts, int $offset): int
    {
        return ((int)$parts[$offset] * 3600 + (int)$parts[$offset + 1] * 60
            + (int)$parts[$offset + 2]) * 1000 + (int)$parts[$offset + 3];
    }

    public static function parseSrt(mixed $input): array
    {
        if (!is_string($input) || strlen($input) > 1048576 || !mb_check_encoding($input, 'UTF-8')) {
            throw new UnprocessableEntityHttpException('请选择 1 MiB 以内的 UTF-8 SRT 文件。');
        }
        $source = str_replace(["\r\n", "\r"], "\n", preg_replace('/^\xEF\xBB\xBF/', '', $input));
        $blocks = preg_split('/\n(?:[ \t]*\n)+/', trim($source), 10001);
        if (count($blocks) > 10000) {
            throw new UnprocessableEntityHttpException('字幕最多支持 10,000 条。');
        }
        $pattern = '/^' . self::TIMESTAMP . '[ \t]*-->[ \t]*' . self::TIMESTAMP . '$/D';
        $cues = [];
        foreach ($blocks as $index => $block) {
            $lines = explode("\n", $block);
            $offset = ctype_digit(trim($lines[0])) ? 1 : 0;
            $text = trim(implode("\n", array_slice($lines, $offset + 1)));
            $number = $index + 1;
            if (!preg_match($pattern, trim($lines[$offset] ?? ''), $parts) || $text === '') {
                throw new UnprocessableEntityHttpException("第 {$number} 条字幕缺少有效时间码或正文。");
            }
            $startMs = self::milliseconds($parts, 1);
            $endMs = self::milliseconds($parts, 5);
            if ($endMs <= $startMs) {
                throw new UnprocessableEntityHttpException("第 {$number} 条字幕的结束时间必须晚于开始时间。");
            }
            $cues[] = compact('startMs', 'endMs', 'text');
        }
        usort($cues, static fn ($a, $b) => $a['startMs'] <=> $b['startMs']);
        return $cues;
    }

    private function resources(): Query
    {
        return (new Query())->from('{{%resource}}')->where(['type' => 'subtitle', 'author_id' => $this->owner]);
    }

    private function summary(array $row): array
    {
        $info = json_decode($row['info'], true, flags: JSON_THROW_ON_ERROR);
        // Earlier writes passed a JSON string to Yii's MySQL JSON encoder.
        if (is_string($info)) {
            $info = json_decode($info, true, flags: JSON_THROW_ON_ERROR);
        }
        return [
            'id' => (int)$row['id'], 'fileId' => (int)$row['file_id'], 'name' => $row['name'],
            'createdAt' => $row['created_at'], 'info' => $info,
        ];
    }

    public function listing(int $page, string $search = ''): array
    {
        $query = $this->resources()->andFilterWhere(['like', 'name', trim($search)]);
        $total = (int)$query->count('*', $this->db);
        $rows = $query->orderBy(['id' => SORT_DESC])->limit(20)->offset(($page - 1) * 20)->all($this->db);
        return ['items' => array_map($this->summary(...), $rows), 'total' => $total];
    }

    private function owned(int $id): array
    {
        $row = $this->resources()->andWhere(['id' => $id])->one($this->db);
        if (!$row) {
            throw new NotFoundHttpException('字幕资源不存在。');
        }
        return $row;
    }

    public function view(int $id): array
    {
        $row = $this->owned($id);
        $file = $this->file((int) $row['file_id']);
        $json = SubtitleFiles::read($this->owner, $file);
        return $this->summary($row) + ['cues' => json_decode($json, true, flags: JSON_THROW_ON_ERROR)['cues']];
    }

    private function file(int $id): array
    {
        $file = (new Query())->select(['key', 'md5', 'url'])->from('{{%file}}')
            ->where(['id' => $id, 'user_id' => $this->owner])->one($this->db);
        if (!$file) throw new UnprocessableEntityHttpException('请选择已上传的本人字幕文件。');
        return $file;
    }

    public static function json(array $cues): string
    {
        return json_encode(['version' => 1, 'cues' => $cues], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function requireMedia(int $id, bool $lock): void
    {
        $sql = "SELECT [[id]] FROM {{%resource}} WHERE [[id]] = :id AND [[author_id]] = :owner AND [[type]] IN ('audio','video')";
        if ($lock && $this->db->driverName === 'mysql') {
            $sql .= ' FOR UPDATE';
        }
        if ($this->db->createCommand($sql, [':id' => $id, ':owner' => $this->owner])->queryScalar() === false) {
            throw new NotFoundHttpException('音频或视频素材不存在。');
        }
    }

    private function linkedSubtitle(int $id): ?array
    {
        $row = $this->resources()->select(['id', 'name'])
            ->innerJoin('{{%resource_subtitle}}', '[[subtitle_resource_id]] = [[id]]')
            ->andWhere(['media_resource_id' => $id])->one($this->db);
        return $row ? ['id' => (int)$row['id'], 'name' => $row['name']] : null;
    }

    public function binding(int $id): array
    {
        $this->requireMedia($id, false);
        return ['subtitle' => $this->linkedSubtitle($id)];
    }

    /** Caller holds the account lock until the subtitle transaction commits. */
    public function setBinding(int $id, array $body): array
    {
        $this->requireMedia($id, true);
        $target = $this->bindingId($body, 'subtitleResourceId');
        $previous = $this->bindingId($body, 'previousSubtitleResourceId');
        $current = $this->linkedSubtitle($id);
        $currentId = $current['id'] ?? null;
        if ($currentId === $target) {
            return ['subtitle' => $current];
        }
        $this->checkPreviousBinding($currentId, $previous);
        if ($target === null) {
            $this->db->createCommand()->delete('{{%resource_subtitle}}', ['media_resource_id' => $id])->execute();
            return ['subtitle' => null];
        }
        return $this->linkAsset($id, $this->owned($target));
    }

    public function uploadBound(int $id, array $body): array
    {
        $this->requireMedia($id, true);
        $previous = $this->bindingId($body, 'previousSubtitleResourceId');
        $this->checkPreviousBinding($this->linkedSubtitle($id)['id'] ?? null, $previous);
        $asset = $this->save(null, $body);
        return $this->linkAsset($id, $asset);
    }

    private function bindingId(array $body, string $field): ?int
    {
        if (!array_key_exists($field, $body)
            || ($body[$field] !== null && (!is_int($body[$field]) || $body[$field] <= 0))) {
            throw new UnprocessableEntityHttpException("{$field} 必须是正整数或 null。");
        }
        return $body[$field];
    }

    private function checkPreviousBinding(?int $current, ?int $previous): void
    {
        if ($current !== $previous) {
            throw new ConflictHttpException('字幕绑定已被其他窗口修改，请查看最新绑定后再操作。');
        }
    }

    private function linkAsset(int $id, array $asset): array
    {
        $values = ['subtitle_resource_id' => (int)$asset['id']];
        $this->db->createCommand()->upsert('{{%resource_subtitle}}', ['media_resource_id' => $id] + $values, $values)->execute();
        return ['subtitle' => ['id' => (int)$asset['id'], 'name' => $asset['name']]];
    }

    /** Caller holds the account lock until the subtitle transaction commits. */
    public function save(?int $id, array $body): array
    {
        $row = $id === null ? null : $this->owned($id);
        if ($row && ($body['fileId'] ?? null) !== (int)$row['file_id']) {
            throw new ConflictHttpException('字幕已被其他窗口修改。请保留草稿，重新打开最新版本后再保存。');
        }
        $name = is_string($body['name'] ?? null) ? trim($body['name']) : '';
        if ($name === '' || mb_strlen($name) > 255 || preg_match('/[\x00-\x1f\x{10000}-\x{10ffff}]/u', $name)) {
            throw new UnprocessableEntityHttpException('请输入 1 至 255 字的素材名称，名称暂不支持表情符号或控制字符。');
        }
        $cues = self::parseSrt($body['srt'] ?? null);
        $fileId = $this->bindingId($body, 'uploadedFileId');
        if (!$fileId) throw new UnprocessableEntityHttpException('请先上传字幕 JSON 文件。');
        $file = $this->file($fileId);
        $json = self::json($cues);
        if ($file['key'] !== md5($json) . '.json' || SubtitleFiles::read($this->owner, $file, requirePublic: true) !== $json) {
            throw new UnprocessableEntityHttpException('已上传文件与字幕内容不匹配。');
        }
        $info = ['cueCount' => count($cues), 'durationMs' => max(array_column($cues, 'endMs'))];
        $values = ['name' => $name, 'file_id' => $fileId, 'updater_id' => $this->owner,
            'info' => $info];
        if ($id === null) {
            $this->db->createCommand()->insert('{{%resource}}', $values + [
                'type' => 'subtitle', 'author_id' => $this->owner, 'uuid' => UuidHelper::uuid(),
            ])->execute();
            $id = (int)$this->db->getLastInsertID();
        } else {
            $updated = $this->db->createCommand()->update('{{%resource}}', $values, [
                'id' => $id, 'type' => 'subtitle', 'author_id' => $this->owner, 'file_id' => $row['file_id'],
            ])->execute();
            if ($updated !== 1 && ((int) $row['file_id'] !== $fileId || $row['name'] !== $name)) {
                throw new ConflictHttpException('字幕版本已更新，请重新打开。');
            }
        }
        return $this->summary($this->owned($id)) + ['cues' => $cues];
    }
}
