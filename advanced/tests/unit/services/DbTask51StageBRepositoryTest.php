<?php

namespace tests\unit\services;

use api\modules\v1\services\DbTask51StageBRepository;
use api\modules\v1\services\Task51CoordinatorException;
use PHPUnit\Framework\TestCase;
use yii\db\Command;
use yii\db\Connection;
use yii\db\IntegrityException;
use yii\db\Transaction;

final class DbTask51StageBRepositoryTest extends TestCase
{
    public function testTransactionPinsRepeatableReadAndCommits(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $transaction->expects($this->once())->method('commit');

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('getTransaction')->willReturn(null);
        $connection->expects($this->once())
            ->method('beginTransaction')
            ->with(Transaction::REPEATABLE_READ)
            ->willReturn($transaction);

        $result = (new DbTask51StageBRepository($connection))->transaction(
            static fn(): string => 'complete'
        );

        $this->assertSame('complete', $result);
    }

    public function testTransactionRejectsInheritedTransactionBeforeWork(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getTransaction')->willReturn($this->createStub(Transaction::class));
        $connection->expects($this->never())->method('beginTransaction');

        $repository = new DbTask51StageBRepository($connection);
        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('refuses an inherited database transaction');
        $repository->transaction(static fn(): null => null);
    }

    public function testInsertTreatsOnlyMySql1062AsDuplicate(): void
    {
        $exception = new IntegrityException(
            'Duplicate entry',
            ['23000', 1062, 'Duplicate entry for key'],
            '23000'
        );

        $repository = new DbTask51StageBRepository($this->connectionThrowing($exception, 'mysql'));

        $this->assertFalse($repository->insertExecution(['execution_id' => 'duplicate']));
    }

    public function testInsertRethrowsCheckConstraintIntegrityFailure(): void
    {
        $exception = new IntegrityException(
            'Check constraint violated',
            ['HY000', 3819, 'Check constraint is violated'],
            'HY000'
        );
        $repository = new DbTask51StageBRepository($this->connectionThrowing($exception, 'mysql'));

        $this->expectExceptionObject($exception);
        $repository->insertExecution(['execution_id' => 'invalid']);
    }

    public function testInsertDoesNotTreatAnotherDrivers1062AsMySqlDuplicate(): void
    {
        $exception = new IntegrityException(
            'Driver-specific integrity error',
            ['23000', 1062, 'not MySQL'],
            '23000'
        );
        $repository = new DbTask51StageBRepository($this->connectionThrowing($exception, 'pgsql'));

        $this->expectExceptionObject($exception);
        $repository->insertExecution(['execution_id' => 'invalid']);
    }

    private function connectionThrowing(IntegrityException $exception, string $driverName): Connection
    {
        $command = $this->createStub(Command::class);
        $command->method('insert')->willReturnSelf();
        $command->method('execute')->willThrowException($exception);

        $connection = $this->createStub(Connection::class);
        $connection->method('getDriverName')->willReturn($driverName);
        $connection->method('createCommand')->willReturn($command);

        return $connection;
    }
}
