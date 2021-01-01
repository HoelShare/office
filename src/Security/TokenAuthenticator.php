<?php
declare(strict_types=1);

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('auth-token');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $apiToken = $request->headers->get('auth-token');
        if ($apiToken === null) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $user = $this->entityManager->createQuery(
            'SELECT u
            FROM App\Entity\User u
            INNER JOIN u.ldapTokens t
            WHERE t.token = :apiToken'
        )->setParameter('apiToken', $apiToken)
            ->getOneOrNullResult();

        if ($user === null) {
            throw new UsernameNotFoundException();
        }
        $badge = new UserBadge($apiToken, fn() => $user);
        return new SelfValidatingPassport($badge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
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
