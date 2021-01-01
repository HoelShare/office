<?php
declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception\OfficeException;
use RuntimeException;

class NotSupportedFieldTypeException extends RuntimeException implements OfficeException
{
    public function __construct(
        private string $type,
        private string $objectClass,
    ) {
        parent::__construct();
    }

    public function getErrors(): array
    {
        return [
            'type' => $this->type,
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }
}
