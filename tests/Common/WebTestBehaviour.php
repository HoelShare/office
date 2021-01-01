<?php declare(strict_types=1);

namespace App\Tests\Common;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

trait WebTestBehaviour
{
    abstract protected function getContainer(): ContainerInterface;

    protected function getClient(): HttpKernelBrowser
    {
        return $this->getContainer()->get('test.client');
    }
}
