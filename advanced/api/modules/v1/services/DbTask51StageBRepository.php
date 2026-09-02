<?php

namespace api\modules\v1\services;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;
use yii\db\Connection;
use yii\db\IntegrityException;
use yii\db\Query;

final class DbTask51StageBRepository implements Task51StageBRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function transaction(Closure $operation): mixed
    {
        $transaction = $this->db->beginTransaction();
        try {
            $result = $operation();
            $transaction->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $exception;
        }
    }

    public function now(): DateTimeImmutable
    {
        $raw = (string)$this->db->createCommand('SELECT UTC_TIMESTAMP(3)')->queryScalar();
        $parsed = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.v',
            $raw,
            new DateTimeZone('UTC')
        );
        if (!$parsed instanceof DateTimeImmutable) {
            throw new Task51CoordinatorException(
                Task51CoordinatorException::UNAVAILABLE,
                'Authoritative database clock is unavailable.'
            );
        }

        return $parsed;
    }

    public function insertExecution(array $row): bool
    {
        try {
            $this->db->createCommand()->insert('{{%task51_stage_b_execution}}', $row)->execute();
            return true;
        } catch (IntegrityException $exception) {
            if ($this->isMySqlDuplicateKey($exception)) {
                return false;
            }
            throw $exception;
        }
    }

    public function findExecution(string $executionId, bool $forUpdate = false): ?array
    {
        $query = (new Query())
            ->from('{{%task51_stage_b_execution}}')
            ->where(['execution_id' => $executionId]);
        $command = $query->createCommand($this->db);
        if ($forUpdate) {
            $command->setSql($command->getRawSql() . ' FOR UPDATE');
        }
        $row = $command->queryOne();

        return is_array($row) ? $row : null;
    }

    public function compareAndSwapState(
        string $executionId,
        string $expectedState,
        int $expectedVersion,
        array $changes
    ): bool {
        return $this->db->createCommand()->update(
            '{{%task51_stage_b_execution}}',
            $changes,
            [
                'execution_id' => $executionId,
                'state' => $expectedState,
                'state_version' => $expectedVersion,
            ]
        )->execute() === 1;
    }

    public function appendTransition(array $row): void
    {
        $this->db->createCommand()->insert('{{%task51_stage_b_transition}}', $row)->execute();
    }

    private function isMySqlDuplicateKey(IntegrityException $exception): bool
    {
        $errorInfo = $exception->errorInfo;
        return $this->db->getDriverName() === 'mysql'
            && is_array($errorInfo)
            && (string)($errorInfo[0] ?? '') === '23000'
            && (int)($errorInfo[1] ?? 0) === 1062;
    }
}
