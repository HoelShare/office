<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * @Route(path="/api/login", name="login", methods={"POST"})
     */
    public function loginAction(): void
    {
        // handled by LdapAuthenticator
    }
}
