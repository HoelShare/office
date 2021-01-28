<?php
declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\CorsHeaderSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class CorsHeaderSubscriberTest extends TestCase
{
    public function testListenToResponseEvent(): void
    {
        static::assertArrayHasKey(KernelEvents::RESPONSE, CorsHeaderSubscriber::getSubscribedEvents());
    }

    public function testSetsReturnCodeOkOnOptionRequest(): void
    {
        $subscriber = new CorsHeaderSubscriber();
        $response = new Response(status: 400);
        $event = new ResponseEvent(
            $this->createMock(KernelInterface::class),
            new Request(server: ['REQUEST_METHOD' => Request::METHOD_OPTIONS]),
            0,
            $response,
        );

        $subscriber->addCorsHeader($event);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
