<?php
declare(strict_types=1);

namespace App\Exception;

use Throwable;

interface OfficeException extends Throwable
{
    public function getErrors(): array;
}
