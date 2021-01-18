<?php
declare(strict_types=1);

namespace App\Controller;

use App\User\UserHydrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private UserHydrator $userHydrator,
    ) {
    }

    /**
     * @Route(path="/api/user/me", name="me", methods={"GET"})
     */
    public function meAction(): JsonResponse
    {
        $user = $this->getUser();
        $this->userHydrator->hydrateUser($user);

        return new JsonResponse(['user' => $user]);
    }
}
