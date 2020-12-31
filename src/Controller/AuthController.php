<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * @Route(path="/login", name="login", methods={"POST"})
     */
    public function loginAction(Request $request): JsonResponse
    {
        dd($this->getUser());
    }

    /**
     * @Route(path="/api/user/me", name="me", methods={"GET"})
     */
    public function meAction(): JsonResponse
    {
        return new JsonResponse(['user' => $this->getUser()]);
    }
}
