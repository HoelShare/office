<?php
declare(strict_types=1);

namespace App\Tests\Common;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

trait AuthorizationTrait
{
    abstract protected function getContainer(): ContainerInterface;

    protected function authorizeUser(User $user): void
    {
        $token = new PostAuthenticationToken($user, 'login', $user->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($token);
    }

    /**
     * @after
     */
    protected function resetAuth(): void
    {
        $this->getContainer()->get('security.token_storage')->setToken();
    }
}
