<?php
declare(strict_types=1);

namespace App\Request;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function __construct(
        private ErrorResponseFactory $errorResponseFactory,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): ExceptionEvent
    {
        $exception = $event->getThrowable();

        $event->setResponse($this->errorResponseFactory->getResponseFromException($exception));

        return $event;
    }
}
