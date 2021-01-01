<?php
declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception\OfficeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WrongDateFormatException extends HttpException implements OfficeException
{
    public function __construct(
        private string $dateValue,
        private string $expectedFormat,
    ) {
        parent::__construct(Response::HTTP_NOT_ACCEPTABLE);
    }

    public function getErrors(): array
    {
        return [
            'expectedFormat' => $this->expectedFormat,
            'currentValue' => $this->dateValue,
        ];
    }
}
