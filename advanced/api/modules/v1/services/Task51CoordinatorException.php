<?php

namespace api\modules\v1\services;

use RuntimeException;

final class Task51CoordinatorException extends RuntimeException
{
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const CONFLICT = 'conflict';
    public const UNAVAILABLE = 'unavailable';

    public function __construct(
        private readonly string $reason,
        string $message
    ) {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
