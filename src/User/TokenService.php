<?php
declare(strict_types=1);

namespace App\User;

use App\Entity\User;

class TokenService
{
    public function generateToken(User $user): string
    {
        return uniqid('', more_entropy: true);
    }
}
