<?php
declare(strict_types=1);

namespace App\Request;

use App\Exception\OfficeException;
use App\Exception\OfficeHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use function is_array;

class ErrorResponseFactory
{
    public function __construct(
        private bool $debug,
    ) {
    }

    public function getResponseFromException(Throwable $throwable): Response
    {
        $response = new JsonResponse(
            null,
            $this->getStatusCodeFromException($throwable),
        );

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_INVALID_UTF8_SUBSTITUTE);
        $response->setData(['errors' => $this->getErrorsFromException($throwable)]);

        return $response;
    }

    public function getErrorsFromException(Throwable $exception): array
    {
        if ($exception instanceof OfficeException) {
            $errors = $exception->getErrors();

            if ($this->debug) {
                $errors['meta'] = $this->addDebugInfo($exception);
            }

            return $this->convert($errors);
        }

        return [$this->convertExceptionToError($exception)];
    }

    private function getStatusCodeFromException(Throwable $exception): int
    {
        if ($exception instanceof OfficeHttpException || $exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function addDebugInfo(Throwable $throwable): array
    {
        $error = [
            'trace' => $this->convert($throwable->getTrace()),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ];

        if ($throwable->getPrevious()) {
            $error['previous'][] = $this->convertExceptionToError($throwable->getPrevious());
        }

        return $error;
    }

    private function convertExceptionToError(Throwable $exception): array
    {
        $statusCode = $this->getStatusCodeFromException($exception);

        $error = [
            'code' => (string) $exception->getCode(),
            'status' => (string) $statusCode,
            'title' => Response::$statusTexts[$statusCode] ?? 'unknown status',
            'detail' => $exception->getMessage(),
        ];

        if ($this->debug) {
            $error['meta'] = $this->addDebugInfo($exception);
        }

        return $error;
    }

    private function convert(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->convert($value);
            }
        }

        return $array;
    }
}
