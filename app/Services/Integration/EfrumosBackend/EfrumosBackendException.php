<?php

namespace App\Services\Integration\EfrumosBackend;

use RuntimeException;
use Throwable;

class EfrumosBackendException extends RuntimeException
{
    public function __construct(string $message, private readonly int $statusCode = 503, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
