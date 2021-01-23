<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Saml\SamlService;
use App\User\UserHydrator;
use App\User\UserService;
use SimpleSAML\Auth\Simple;
use SimpleSAML\Module;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SamlAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private SamlService $samlService,
        private UserService $userService,
        private UserHydrator $userHydrator,
        private string $authService,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->authService === 'saml';
    }

    public function authenticate(Request $request): PassportInterface
    {
        $authSource = new Simple('default-sp');
        $authSource->requireAuth();

        $user = $this->samlService->updateUser($authSource);

        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        $this->userService->addToken($user);
        $userBadge = new UserBadge($user->getExternalId(), fn () => $user);

        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $authToken = null;
        $user = $token->getUser();
        if ($user instanceof User && $user->getAuthTokens()->last() !== null) {
            $authToken = $user->getAuthTokens()->last()->getToken();
        }

        $this->userHydrator->hydrateUser($user);

        $data = [
            'authToken' => $authToken,
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