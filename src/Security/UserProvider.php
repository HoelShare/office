<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // TODO: implement
        // ignore for now
    }

    public function loadUserByUsername(string $username): void
    {
        // ignore switch_user and remember_me is disabled
    }

    public function refreshUser(UserInterface $user): void
    {
        // Ignore for stateless Routing
    }

    public function supportsClass(string $class)
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
