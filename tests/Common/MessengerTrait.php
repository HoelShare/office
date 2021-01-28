<?php
declare(strict_types=1);

namespace App\Tests\Common;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

trait MessengerTrait
{
    abstract protected function getContainer(): ContainerInterface;

    /**
     * @after
     */
    public function clearMessenger(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $transport->reset();
    }
}
