<?php declare(strict_types=1);

namespace App\Tests\Ldap;

use App\Ldap\LdapProvider;
use App\Ldap\UserMapper;
use PHPUnit\Framework\TestCase;

class LdapProviderTest extends TestCase
{
    public function testWithInvalidServerCredentials(): void
    {
        $provider = new LdapProvider(
            uniqid('', true),
            uniqid('', true),
            uniqid('', true),
            uniqid('', true),
            $this->createMock(UserMapper::class),
        );

        $result = $provider->initializeConnection(uniqid('', true), uniqid('', true));

        static::assertFalse($result);
    }
}
