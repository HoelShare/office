<?php
declare(strict_types=1);

namespace App\Ldap;

use App\Entity\User;
use App\User\UserService;

class LdapService
{
    public function __construct(
        private LdapProvider $ldapProvider,
        private UserService $userService,
) {
    }

    public function updateUser(string $username, string $password): ?User
    {
        if (!$this->ldapProvider->initializeConnection($username, $password)) {
            return null;
        }

        $user = $this->ldapProvider->getUserData($username);

        return $this->userService->updateUser($user);
    }
}
