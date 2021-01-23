<?php declare(strict_types=1);

namespace App\Controller;

use SimpleSAML\Auth\Simple;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SamlAuthController extends AbstractController
{
    public function __construct(
    ) {
    }

    /**
     * @Route(path="/api/saml/callback", name="authSaml", methods={"POST"})
     */
    public function authSamlAction(Request $request): JsonResponse
    {
        require_once(__DIR__.'/../../vendor/simplesamlphp/simplesamlphp/lib/_autoload.php');
        $authSource = new Simple('default-sp');
       // $authSource->requireAuth();
        dd($authSource->isAuthenticated());
        return new JsonResponse();
    }
}