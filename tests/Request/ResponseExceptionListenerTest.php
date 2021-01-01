<?php declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\ResponseExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseExceptionListenerTest extends TestCase
{
    public function testListensOnExceptions(): void
    {
        static::assertArrayHasKey(KernelEvents::EXCEPTION, ResponseExceptionListener::getSubscribedEvents());
    }
}
