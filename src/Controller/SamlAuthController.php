<?php
declare(strict_types=1);

namespace App\Controller;

use SimpleSAML\Auth\Simple;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SamlAuthController extends AbstractController
{
    /**
     * @Route(path="/api/saml/callback", name="authSaml", methods={"POST"})
     */
    public function authSamlAction(Request $request): JsonResponse
    {
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['PATH_INFO'] = '/default-sp';
        require __DIR__ . '/../../vendor/simplesamlphp/simplesamlphp/modules/saml/www/sp/saml2-acs.php';

        $authSource = new Simple('default-sp');
        $authSource->requireAuth();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
