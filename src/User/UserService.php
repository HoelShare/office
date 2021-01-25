<?php
declare(strict_types=1);

namespace App\User;

use App\Entity\AuthToken;
use App\Entity\User;
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
        $token = new AuthToken();
        $token->setUser($user);
        $token->setToken($this->tokenService->generateToken($user));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $user->addAuthToken($token);
    }

    public function removeToken(User $user, string $token): void
    {
        foreach ($user->getAuthTokens() as $authToken) {
            if ($authToken->getToken() === $token) {
                $user->removeAuthToken($authToken);
                $this->entityManager->remove($authToken);
            }
        }

        $this->entityManager->flush();
    }

    public function updateUser(ImportUser $importUser): User
    {
        $user = $this->getUserById($importUser->id);
        $user->setEmail($importUser->email);
        $user->setFullName($importUser->fullName);
        $user->setImage($importUser->image);
        $user->setName($importUser->displayName);
        $user->setRoles($importUser->roles);

        $this->syncUser($user);

        return $user;
    }

    private function syncUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getUserById(string $externalId): User
    {
        /** @var User|null $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['externalId' => $externalId]);

        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setExternalId($externalId);

        return $user;
    }
}
