<?php

namespace tests\unit\services;

use api\modules\v1\services\DbTask51StageBRepository;
use PHPUnit\Framework\TestCase;
use yii\db\Command;
use yii\db\Connection;
use yii\db\IntegrityException;

final class DbTask51StageBRepositoryTest extends TestCase
{
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
