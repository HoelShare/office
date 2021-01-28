<?php
declare(strict_types=1);

namespace App\Tests\Repository\Event;

use App\Repository\Event\WebhookWriteListener;
use App\Repository\Event\WriteEvent;
use PHPUnit\Framework\TestCase;

class WebhookWriteListenerTest extends TestCase
{
    public function testListensToWriteEvents(): void
    {
        static::assertArrayHasKey(WriteEvent::class, WebhookWriteListener::getSubscribedEvents());
    }
}
