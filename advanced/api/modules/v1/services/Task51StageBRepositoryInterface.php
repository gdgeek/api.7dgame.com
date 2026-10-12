<?php

namespace api\modules\v1\services;

use Closure;
use DateTimeImmutable;

interface Task51StageBRepositoryInterface
{
    public function transaction(Closure $operation): mixed;

    public function now(): DateTimeImmutable;

    /** @param array<string, mixed> $row */
    public function insertExecution(array $row): bool;

    /** @return array<string, mixed>|null */
    public function findExecution(string $executionId, bool $forUpdate = false): ?array;

    /** @param array<string, mixed> $changes */
    public function compareAndSwapState(
        string $executionId,
        string $expectedState,
        int $expectedVersion,
        array $changes
    ): bool;

    /** @param array<string, mixed> $row */
    public function appendTransition(array $row): void;
}
