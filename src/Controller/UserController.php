<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route(path="/api/user/me", name="me", methods={"GET"})
     */
    public function meAction(): JsonResponse
    {
        return new JsonResponse(['user' => $this->getUser()]);
    }
}
