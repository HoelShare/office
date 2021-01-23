<?php
declare(strict_types=1);

namespace App\User;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserHydrator
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function hydrateUser(User $user): void
    {
        $user->setIsAdmin($this->authorizationChecker->isGranted('ROLE_ADMIN'));
    }
}
