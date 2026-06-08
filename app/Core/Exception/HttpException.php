<?php

declare(strict_types=1);

namespace App\Core\Exception;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?: ('HTTP ' . $statusCode), 0, $previous);
    }
}
