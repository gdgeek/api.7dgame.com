<?php

namespace api\modules\v1\services;

use yii\db\Connection;

/** Idempotent display projection. No event payload can set credentials. */
class IdentityUserProjection
{
    public function apply(Connection $db, array $event): bool
    {
        $subject = $event['subject'] ?? [];
        $id = $event['userId'] ?? null;
        $version = $event['version'] ?? null;
        if (!is_int($id) || $id <= 0 || !is_int($version) || $version <= 0
            || ($subject['id'] ?? null) !== $id || !is_string($subject['username'] ?? null)
            || !is_int($subject['status'] ?? null)) {
            throw new \UnexpectedValueException('Invalid Identity projection event.');
        }

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->upsert('{{%identity_user_projection_state}}', [
                'user_id' => $id, 'version' => 0,
            ], false)->execute();
            $current = $db->createCommand('SELECT [[version]] FROM {{%identity_user_projection_state}} WHERE [[user_id]]=:id FOR UPDATE', [':id' => $id])->queryScalar();
            if ((int)$current >= $version) {
                $transaction->commit();
                return false;
            }

            $attributes = [
                'username' => $subject['username'], 'nickname' => $subject['nickname'] ?? null,
                'email' => $subject['email'] ?? null, 'status' => $subject['status'],
                'email_verified_at' => $subject['emailVerifiedAt'] ?? null,
                'auth_key' => '', 'password_hash' => '!identity-owned',
                'password_reset_token' => null, 'updated_at' => time(),
            ];
            $exists = $db->createCommand('SELECT [[id]] FROM {{%user}} WHERE [[id]]=:id FOR UPDATE', [':id' => $id])->queryScalar();
            if ($exists === false) {
                $db->createCommand()->insert('{{%user}}', array_merge($attributes, [
                    'id' => $id, 'created_at' => time(),
                ]))->execute();
            } else {
                // MySQL upsert resolves any UNIQUE key, including a stale
                // email belonging to another projection. Update by the stable
                // PK only so such a collision fails without changing owners.
                $db->createCommand()->update('{{%user}}', $attributes, ['id' => $id])->execute();
            }
            $exists = (new \yii\db\Query())->from('{{%user_info}}')->where(['user_id' => $id])->exists($db);
            if (!$exists) {
                $db->createCommand()->insert('{{%user_info}}', ['user_id' => $id])->execute();
            }
            $db->createCommand()->update('{{%identity_user_projection_state}}', ['version' => $version], ['user_id' => $id])->execute();
            $transaction->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $exception;
        }
    }
}
