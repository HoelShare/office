<?php
declare(strict_types=1);

namespace App\Repository\Event;

use App\Entity\User;
use App\Entity\Webhook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class WebhookWriteListener implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WriteEvent::class => 'writeWebhook',
        ];
    }

    public function writeWebhook(WriteEvent $event): void
    {
        if ($event->getClass() !== Webhook::class) {
            return;
        }

        /** @var Webhook $webhook */
        $webhook = $event->getObject();

        $this->checkUser($webhook);
    }

    private function checkUser(Webhook $webhook): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $webhook->setUser($user);
    }
}
