<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Ldap\LdapService;
use App\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LdapAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private LdapService $ldapService,
        private UserService $userService,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if ($username === null || $password === null) {
            throw new UsernameNotFoundException();
        }

        $user = $this->ldapService->updateUser($username, $password);

        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        $this->userService->addToken($user);

        return new SelfValidatingPassport($user);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $authToken = null;
        $user = $token->getUser();
        if ($user instanceof User && $user->getLdapTokens()->last() !== null) {
            $authToken = $user->getLdapTokens()->last()->getToken();
        }

        $data = [
            'auth_token' => $authToken,
            'user' => $user,
        ];

        return new JsonResponse($data);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
