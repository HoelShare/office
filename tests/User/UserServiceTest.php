<?php declare(strict_types=1);

namespace App\Tests\User;

use App\Ldap\LdapUser;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use App\User\UserService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    use IntegrationTestBehaviour;
    use DemodataTrait;

    private UserService $userService;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->userService = $this->getContainer()->get(UserService::class);
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->addCommonData();
    }

    public function testAddsTokenToUser(): void
    {
        static::assertCount(1, $this->user->getLdapTokens());
        $this->userService->addToken($this->user);

        static::assertCount(2, $this->user->getLdapTokens());
    }

    public function testAddsTokenToDatabase(): void
    {
        $countBefore = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM ldap_token');
        $this->userService->addToken($this->user);
        $countAfter = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM ldap_token');

        static::assertSame($countBefore + 1, $countAfter);
    }

    public function testAddedTokenBelongsToUser(): void
    {
        $this->userService->addToken($this->user);
        $token = $this->user->getLdapTokens()->first();
        $userId = (int)$this->connection->fetchOne('SELECT user_id FROM ldap_token where id = :id', ['id' => $token->getId()]);

        static::assertSame($this->user->getId(), $userId);
        static::assertSame($this->user->getId(), $token->getUser()->getId());
    }

    public function testUpdateWritesUser(): void
    {
        $ldapUser = new LdapUser();
        $ldapUser->id = uniqid('', true);
        $ldapUser->roles = [];
        $ldapUser->email = 'test@example.com';
        $ldapUser->displayName = 'Test';
        $ldapUser->fullName = 'Full Name';

        $user = $this->userService->updateUser($ldapUser);

        static::assertSame($ldapUser->id, $user->getLdapId());
        static::assertSame(['ROLE_USER'], $user->getRoles());
        static::assertSame($ldapUser->email, $user->getEmail());
        static::assertSame($ldapUser->displayName, $user->getName());
        static::assertSame($ldapUser->fullName, $user->getFullName());
    }

    public function testUpdateCreatesNotExisting(): void
    {
        $ldapUser = new LdapUser();
        $ldapUser->id = uniqid('', true);
        $ldapUser->roles = [];
        $ldapUser->email = 'test@example.com';
        $ldapUser->displayName = 'Test';
        $ldapUser->fullName = 'Full Name';

        $this->userService->updateUser($ldapUser);

        $user = $this->connection->fetchAssociative('SELECT * from user where ldap_id = :ldap', ['ldap' => $ldapUser->id]);

        static::assertNotEquals(false, $user, 'User was not created');
        static::assertSame($ldapUser->id, $user['ldap_id']);
        static::assertSame($ldapUser->fullName, $user['full_name']);
        static::assertSame($ldapUser->displayName, $user['name']);
        static::assertSame($ldapUser->email, $user['email']);
        static::assertSame($ldapUser->roles, json_decode($user['roles'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function testUpdateSyncExistingUser(): void
    {
        $ldapUser = new LdapUser();
        $ldapUser->id = $this->user->getLdapId();
        $ldapUser->roles = [];
        $ldapUser->email = 'test@example.com';
        $ldapUser->displayName = 'Test';
        $ldapUser->fullName = 'Full Name';

        $this->userService->updateUser($ldapUser);

        $user = $this->connection->fetchAssociative('SELECT * from user where ldap_id = :ldap', ['ldap' => $ldapUser->id]);

        static::assertNotEquals(false, $user, 'User was not created');
        static::assertSame($ldapUser->id, $user['ldap_id']);
        static::assertSame($ldapUser->fullName, $user['full_name']);
        static::assertSame($ldapUser->displayName, $user['name']);
        static::assertSame($ldapUser->email, $user['email']);
        static::assertSame($ldapUser->roles, json_decode($user['roles'], true, 512, JSON_THROW_ON_ERROR));
    }
}
