<?php
declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\JsonRequestTransformerListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class JsonRequestTransformerListenerTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        static::assertArrayHasKey(KernelEvents::REQUEST, JsonRequestTransformerListener::getSubscribedEvents());
    }

    public function testThrowsErrorOnInvalidJson(): void
    {
        $request = new Request(server: ['HTTP_content-type' => 'application/json'], content: '{"value", "key": { "nested" }}');
        $event = new RequestEvent($this->createMock(KernelInterface::class), $request, null);

        $transformer = new JsonRequestTransformerListener();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The JSON payload is malformed.');
        $transformer->onRequest($event);
    }
}
