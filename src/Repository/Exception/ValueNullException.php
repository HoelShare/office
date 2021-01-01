<?php
declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception\OfficeException;
use RuntimeException;

class ValueNullException extends RuntimeException implements OfficeException
{
    public function getErrors(): array
    {
        return [
            'error' => 'Value should not be blank',
        ];
    }
}
