<?php
declare(strict_types=1);

namespace App\User;

use App\Entity\LdapToken;
use App\Entity\User;
use App\Ldap\LdapUser;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenService $tokenService,
    ) {
    }

    public function addToken(User $user): void
    {
        $token = new LdapToken();
        $token->setUser($user);
        $token->setToken($this->tokenService->generateToken($user));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $user->addLdapToken($token);
    }

    public function updateUser(LdapUser $ldapUser): User
    {
        $user = $this->getUserById($ldapUser->id);
        $user->setEmail($ldapUser->email);
        $user->setFullName($ldapUser->fullName);
        $user->setImage($ldapUser->image);
        $user->setName($ldapUser->displayName);
        $user->setRoles($ldapUser->roles);

        $this->syncUser($user);

        return $user;
    }

    private function syncUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getUserById(string $ldapId): User
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['ldapId' => $ldapId]);

        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setLdapId($ldapId);

        return $user;
    }
}
