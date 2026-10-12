<?php

namespace api\modules\v1\services;

use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
/** Stores caller-owned orchestration evidence. It never executes an editor operation. */
final class AuthoringTaskStore
{
    public const TABLE = '{{%webmcp_authoring_task}}';
    private const LEASE_SECONDS = 120;
    private const MAX_BYTES = 1048576;
    private const TOOLS = [
        'xrugc_stage_authoring_creation',
        'xrugc_complete_authoring_draft',
        'xrugc_open_authoring_object',
        'xrugc_stage_object_cover',
        'xrugc_get_object_cover',
        'xrugc_search_authoring_assets',
        'xrugc_get_asset_metadata',
        'xrugc_open_entity_script_editor',
        'xrugc_close_entity_script_editor',
        'xrugc_open_scene_script_editor',
        'xrugc_close_scene_script_editor',
        'xrugc_stage_resource_placement',
        'xrugc_complete_resource_placement',
        'xrugc_stage_scene_entity_placement',
        'xrugc_complete_scene_entity_placement',
        'xrugc_stage_script_block_batch',
        'xrugc_complete_script_block_batch',
        'xrugc_stage_meta_script_replace',
        'xrugc_complete_meta_script_replace',
        'xrugc_stage_scene_script_replace',
        'xrugc_complete_scene_script_replace',
        'xrugc_get_entity_tree',
        'xrugc_get_scene_modules',
        'xrugc_get_script_block_catalog',
        'xrugc_validate_meta_script',
        'xrugc_validate_scene_script',
    ];
    private static function actor(): int
    {
        $id = (int) Yii::$app->user->id;
        if ($id < 1) {
            throw new UnauthorizedHttpException('Authentication required');
        }
        return $id;
    }
    private static function id(string $id): string
    {
        if (!ReliableWrite::validId($id)) {
            throw new BadRequestHttpException('Invalid task or claim ID');
        }
        return strtolower($id);
    }
    private static function encode(array $value, int $max = self::MAX_BYTES): string
    {
        $json = ContentRevision::canonical($value);
        if (strlen($json) > $max) {
            throw new BadRequestHttpException('Task evidence too large');
        }
        return $json;
    }
    private static function row(string $id, bool $lock = false): array
    {
        $db = Yii::$app->db;
        $id = self::id($id);
        $actor = self::actor();
        $table = $db->quoteTableName(self::TABLE);
        if ($lock && $db->driverName === 'sqlite') {
            $db->createCommand("UPDATE {$table} SET revision=revision WHERE task_id=:id AND actor_id=:actor", [':id' => $id, ':actor' => $actor])->execute();
        }
        $row = $db->createCommand("SELECT * FROM {$table} WHERE task_id=:id AND actor_id=:actor" . ($lock && $db->driverName !== 'sqlite' ? ' FOR UPDATE' : ''), [':id' => $id, ':actor' => $actor])->queryOne();
        if ($row === false) {
            throw new NotFoundHttpException('Task not found');
        }
        return $row;
    }
    private static function decode(string $json): array
    {
        $restore = function ($value) use (&$restore) {
            if ($value instanceof \stdClass) {
                $fields = get_object_vars($value);
                return $fields === [] ? new \stdClass() : array_map($restore, $fields);
            }
            return is_array($value) ? array_map($restore, $value) : $value;
        };
        return $restore(json_decode($json, false, 64, JSON_THROW_ON_ERROR));
    }
    private static function publicRow(array $row): array
    {
        return [
            'taskId' => $row['task_id'],
            'name' => $row['name'],
            'revision' => (int) $row['revision'],
            'plan' => self::decode($row['plan']),
            'progress' => self::decode($row['progress']),
            'leaseUntil' => (int) $row['lease_until'],
            'updatedAt' => (int) $row['updated_at'],
            'persistence' => 'server',
            'resultEvidence' => 'client_reported; verify writes using server operation receipts',
        ];
    }
    public static function create(array $body): array
    {
        $id = self::id((string) ($body['taskId'] ?? ''));
        $actor = self::actor();
        $name = $body['name'] ?? null;
        $steps = $body['steps'] ?? null;
        if (!is_string($name) || trim($name) === '' || mb_strlen($name) > 120 || !is_array($steps) || !array_is_list($steps) || count($steps) < 1 || count($steps) > 30) {
            throw new BadRequestHttpException('Invalid task plan');
        }
        $keys = [];
        foreach ($steps as $step) {
            if (!is_array($step) || !is_string($step['key'] ?? null) || !preg_match('/\A[a-zA-Z][a-zA-Z0-9_-]{0,40}\z/D', $step['key']) || isset($keys[$step['key']]) || !in_array($step['tool'] ?? null, self::TOOLS, true) || !is_array($step['input'] ?? null) && !($step['input'] ?? null) instanceof \stdClass || array_diff(array_keys($step), ['key', 'tool', 'input', 'target'])) {
                throw new BadRequestHttpException('Invalid task step');
            }
            if (in_array($step['key'], ['constructor', 'prototype', '__proto__'], true)) {
                throw new BadRequestHttpException('Invalid task key');
            }
            $keys[$step['key']] = true;
        }
        $plan = self::encode($steps, 262144);
        $db = Yii::$app->db;
        return $db->useMaster(function () use ($db, $id, $actor, $name, $steps, $plan) {
            $existing = (new Query())->from(self::TABLE)->where(['actor_id' => $actor, 'task_id' => $id])->one($db);
            if ($existing !== false) {
                if ($existing['plan'] !== $plan || $existing['name'] !== $name) {
                    throw new ConflictHttpException('Task ID already has another plan');
                }
                return self::publicRow($existing);
            }
            if ((new Query())->from(self::TABLE)->where(['actor_id' => $actor])->count('*', $db) >= 200) {
                throw new ConflictHttpException('Task quota reached; retain/export existing evidence before creating more tasks');
            }
            $progress = [
                'index' => 0,
                'status' => 'preview',
                'states' => array_map(fn($s) => ['key' => $s['key'], 'status' => 'planned'], $steps),
            ];
            try {
                $db->createCommand()->insert(self::TABLE, [
                    'task_id' => $id,
                    'actor_id' => $actor,
                    'name' => $name,
                    'plan' => $plan,
                    'progress' => self::encode($progress),
                    'revision' => 1,
                    'lease_id' => null,
                    'lease_until' => 0,
                    'created_at' => time(),
                    'updated_at' => time(),
                ])->execute();
            } catch (\yii\db\IntegrityException $error) {
                $row = self::row($id);
                if ($row['plan'] !== $plan || $row['name'] !== $name) {
                    throw new ConflictHttpException('Task ID already has another plan');
                }
            }
            return self::publicRow(self::row($id));
        });
    }
    public static function get(string $id): array
    {
        return Yii::$app->db->useMaster(fn() => self::publicRow(self::row($id)));
    }
    public static function listing(int $offset = 0): array
    {
        if ($offset < 0 || $offset > 100000) {
            throw new BadRequestHttpException('Invalid task offset');
        }
        $db = Yii::$app->db;
        return $db->useMaster(function () use ($db, $offset) {
            $rows = (new Query())->from(self::TABLE)->where(['actor_id' => self::actor()])->orderBy(['updated_at' => SORT_DESC, 'task_id' => SORT_ASC])->offset($offset)->limit(21)->all($db);
            return [
                'items' => array_map(function ($r) {
                    $p = json_decode($r['progress'], true, 64, JSON_THROW_ON_ERROR);
                    return [
                        'taskId' => $r['task_id'],
                        'name' => $r['name'],
                        'revision' => (int) $r['revision'],
                        'status' => $p['status'],
                        'index' => $p['index'],
                        'updatedAt' => (int) $r['updated_at'],
                    ];
                }, array_slice($rows, 0, 20)),
                'nextOffset' => count($rows) > 20 ? $offset + 20 : null,
            ];
        });
    }
    public static function change(string $id, array $body, string $action): array
    {
        $claim = self::id((string) ($body['claimId'] ?? ''));
        $version = $body['revision'] ?? null;
        if (!is_int($version) || $version < 1) {
            throw new BadRequestHttpException('Task revision required');
        }
        $db = Yii::$app->db;
        return $db->useMaster(function () use ($db, $id, $claim, $version, $body, $action) {
            $tx = $db->beginTransaction();
            try {
                $row = self::row($id, true);
                if ((int) $row['revision'] !== $version) {
                    throw new ConflictHttpException('Task progress changed; reload before continuing');
                }
                if ($action === 'claim') {
                    if ((int) $row['lease_until'] > time() && $row['lease_id'] !== $claim) {
                        throw new ConflictHttpException('Another client is advancing this task');
                    }
                    $changes = ['lease_id' => $claim, 'lease_until' => time() + self::LEASE_SECONDS];
                } elseif ($action === 'checkpoint') {
                    if ($row['lease_id'] !== $claim || (int) $row['lease_until'] <= time()) {
                        throw new ConflictHttpException('Task lease expired; read progress and original receipt');
                    }
                    $progress = $body['progress'] ?? null;
                    $steps = json_decode($row['plan'], true, 64, JSON_THROW_ON_ERROR);
                    if (!is_array($progress) || array_diff(array_keys($progress), ['index', 'status', 'states']) || !is_int($progress['index'] ?? null) || $progress['index'] < 0 || $progress['index'] > count($steps) || !is_string($progress['status'] ?? null) || strlen($progress['status']) > 40 || !is_array($progress['states'] ?? null) || !array_is_list($progress['states']) || count($progress['states']) !== count($steps)) {
                        throw new BadRequestHttpException('Invalid task progress');
                    }
                    foreach ($progress['states'] as $i => $state) {
                        if (!is_array($state) || ($state['key'] ?? null) !== $steps[$i]['key'] || !is_string($state['status'] ?? null) || strlen($state['status']) > 40 || array_diff(array_keys($state), ['key', 'status', 'result', 'operationId', 'target'])) {
                            throw new BadRequestHttpException('Invalid step evidence');
                        }
                    }
                    $changes = ['progress' => self::encode($progress), 'lease_until' => time() + self::LEASE_SECONDS];
                    if (($body['release'] ?? false) === true) {
                        $changes = array_merge($changes, ['lease_id' => null, 'lease_until' => 0]);
                    }
                } else {
                    throw new BadRequestHttpException('Unknown task action');
                }
                $changes['revision'] = $version + 1;
                $changes['updated_at'] = time();
                $db->createCommand()->update(self::TABLE, $changes, ['task_id' => $row['task_id'], 'actor_id' => self::actor()])->execute();
                $result = self::publicRow(array_merge($row, $changes));
                $tx->commit();
                return $result;
            } catch (\Throwable $e) {
                if ($tx->isActive) {
                    $tx->rollBack();
                }
                throw $e;
            }
        });
    }
}
