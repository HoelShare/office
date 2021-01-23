<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    /**
     * @Route(path="/api/login", name="login", methods={"POST", "GET"})
     */
    public function loginAction(): void
    {
        // handled by Authenticator
    }

    /**
     * @Route(path="/api/logout", name="logout", methods={"GET", "POST", "DELETE"})
     */
    public function logoutAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $token = $request->headers->get('auth-token');

        $this->userService->removeToken($user, $token);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
