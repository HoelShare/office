<?php
declare(strict_types=1);

namespace App\Messenger;

use Symfony\Component\Security\Core\User\UserInterface;

class UpdateMessage extends CreateMessage
{
    public function __construct(
        string $entity,
        object $data,
        ?UserInterface $user,
        private array $updatedData,
    ) {
        parent::__construct($entity, $data, $user);
    }

    public function getUpdatedData(): array
    {
        return $this->updatedData;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'type' => 'update',
                'updated' => $this->getUpdatedData(),
            ]
        );
    }
}
