<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class RegionApiException extends RuntimeException
{
    public function __construct(
        string $message = 'Layanan data wilayah sedang tidak tersedia. Coba lagi.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
