<?php
declare(strict_types=1);

namespace App\Exception;

interface OfficeHttpException extends OfficeException
{
    public function getStatusCode(): int;
}
