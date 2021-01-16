<?php
declare(strict_types=1);

namespace App\Tests\Ldap;

use App\Ldap\UserMapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserMapperTest extends TestCase
{
    private UserMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new UserMapper([
            'id' => 'id',
            'email' => 'email',
            'display_name' => 'display_name',
            'full_name' => 'full_name',
            'roles' => 'roles',
            'image' => 'image',
        ]);
    }

    public function testMappingWithInvalidPath(): void
    {
        $user = $this->mapper->mapUserInfo(['not' => 'every', 'key' => 'is', 'set']);

        static::assertNull($user->id);
        static::assertNull($user->email);
        static::assertNull($user->roles);
        static::assertNull($user->displayName);
        static::assertNull($user->fullName);
        static::assertNull($user->image);
    }

    public function testMappingWithValidPaths(): void
    {
        $user = $this->mapper->mapUserInfo([
            'id' => ['id'],
            'email' => ['email'],
            'roles' => ['ROLE_USER'],
            'display_name' => ['Display Name'],
            'full_name' => ['Full Name'],
            'image' => ['BINARY IMAGE DATA'],
        ]);
        static::assertSame('id', $user->id);
        static::assertSame('email', $user->email);
        static::assertSame(['ROLE_USER'], $user->roles);
        static::assertSame('Display Name', $user->displayName);
        static::assertSame('Full Name', $user->fullName);
        static::assertSame('BINARY IMAGE DATA', $user->image);
    }
}
