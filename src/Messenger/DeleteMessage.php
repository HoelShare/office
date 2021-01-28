<?php
declare(strict_types=1);

namespace App\Messenger;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class DeleteMessage implements WebhookMessageInterface
{
    public function __construct(
        private string $entity,
        private string $id,
        private ?UserInterface $user,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'delete',
            'entity' => $this->getEntity(),
            'id' => $this->getId(),
            'user' => $this->getUser(),
        ];
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): ?UserInterface
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
}
