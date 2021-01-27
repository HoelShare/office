<?php declare(strict_types=1);

namespace App\Messenger;

use App\Entity\User;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateMessage implements WebhookMessageInterface
{
    public function __construct(
        private string $entity,
        private object $data,
        private UserInterface $user,
    ) {
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getData(): object
    {
        return $this->data;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        if (!($this->user instanceof User)) {
            return null;
        }

        return $this->user->getId();
    }

    public function toArray(): array
    {
        return [
            'type' => 'create',
            'entity' => $this->getEntity(),
            $this->getEntity() => $this->getData(),
            'user' => $this->getUser(),
        ];
    }
}