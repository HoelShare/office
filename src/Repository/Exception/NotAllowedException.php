<?php
declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception\OfficeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class NotAllowedException extends HttpException implements OfficeException
{
    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct(Response::HTTP_FORBIDDEN, $message, $previous, $headers, $code);
    }

    public function getErrors(): array
    {
        // TODO: Enrich with useful information
        return [];
    }
}
