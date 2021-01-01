<?php
declare(strict_types=1);

namespace App\Tests\Common;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait KernelTestBehaviour
{
    protected function getKernel(): KernelInterface
    {
        return KernelLifecycleManager::getKernel();
    }

    protected function getContainer(): ContainerInterface
    {
        $container = $this->getKernel()->getContainer();

        if (!$container->has('test.service_container')) {
            throw new RuntimeException('Unable to run tests against kernel without test.service_container');
        }

        return $container->get('test.service_container');
    }

    protected function assertEvent(string $eventName, callable $callback, ?callable $eventCallback = null): void
    {
        $eventDidRun = false;
        $callable = function ($event) use (&$eventDidRun, $eventCallback) {
            $eventDidRun = true;
            if ($eventCallback !== null) {
                $eventCallback($event);
            }
        };
        $this->getContainer()->get(EventDispatcherInterface::class)->addListener($eventName, $callable);

        $callback();

        static::assertTrue($eventDidRun, sprintf('Event \'%s\' did not Run!', $eventName));
        $this->getContainer()->get(EventDispatcherInterface::class)->removeListener($eventName, $callable);
    }
}
