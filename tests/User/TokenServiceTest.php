<?php declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\User;
use App\User\TokenService;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    public function testGenerateToken(): void
    {
        $service = new TokenService();
        $user = new User();
        $token = $service->generateToken($user);

        static::assertSame(spl_object_hash($user), $token);
    }
}
