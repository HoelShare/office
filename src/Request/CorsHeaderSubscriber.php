<?php
declare(strict_types=1);

namespace App\Request;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsHeaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'addCorsHeader',
        ];
    }

    public function addCorsHeader(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
        $response->headers->set('Access-Control-Allow-Origin',
            [$event->getRequest()->headers->get('origin') ?? $event->getRequest()->headers->get('referrer')], true);
        $response->headers->set('Vary', 'Origin');
        $response->headers->set('Access-Control-Allow-Headers', ['Content-Type', 'auth-token'], true);
        $response->headers->set('Access-Control-Allow-Methods', ['GET', 'OPTIONS', 'POST', 'PATCH', 'DELETE'], true);

        if ($event->getRequest()->getMethod() === Request::METHOD_OPTIONS) {
            $response->setStatusCode(Response::HTTP_OK);
        }
    }
}
