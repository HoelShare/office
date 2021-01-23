<?php declare(strict_types=1);

namespace App\Security;

use App\Saml\SamlConfig;
use SimpleSAML\Auth\Simple;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class SamlAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private SamlConfig $config,
        private $useSaml = true,

    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->useSaml && $request->isMethod("GET");
    }

    public function authenticate(Request $request): PassportInterface
    {
        $authSource = new Simple('default-sp');
        $authSource->requireAuth();
        throw new \RuntimeException();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new JsonResponse(['success' => true]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['fail' => true]);
    }
}