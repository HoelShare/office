<?php
declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception\OfficeHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends HttpException implements OfficeHttpException
{
    public function __construct(
        private ConstraintViolationListInterface $constraints
    ) {
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, 'Validation errors...');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function violationsToArray(ConstraintViolationListInterface $violations): array
    {
        $messages = [];

        foreach ($violations as $constraint) {
            $prop = $constraint->getPropertyPath();
            $messages[$prop][] = $constraint->getMessage();
        }

        return $messages;
    }

    public function getErrors(): array
    {
        return $this->violationsToArray($this->constraints);
    }
}
