<?php

namespace App\Saml;

use App\Entity\User;
use App\User\UserMapper;
use App\User\UserService;
use SimpleSAML\Auth\Simple;

class SamlService
{
    public function __construct(
        private UserMapper $userMapper,
        private UserService $userService,
    ) {
    }

    public function updateUser(Simple $auth): ?User
    {
        if (!$auth->isAuthenticated()) {
            return null;
        }

        $user = $this->userMapper->mapUserInfo($auth->getAttributes());

        return $this->userService->updateUser($user);
    }
}