<?php
declare(strict_types=1);

namespace App\Tests\User;

use App\User\ImportUser;
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
        static::assertCount(1, $this->user->getAuthTokens());
        $this->userService->addToken($this->user);

        static::assertCount(2, $this->user->getAuthTokens());
    }

    public function testAddsTokenToDatabase(): void
    {
        $countBefore = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM auth_token');
        $this->userService->addToken($this->user);
        $countAfter = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM auth_token');

        static::assertSame($countBefore + 1, $countAfter);
    }

    public function testAddedTokenBelongsToUser(): void
    {
        $this->userService->addToken($this->user);
        $token = $this->user->getAuthTokens()->first();
        $userId = (int) $this->connection->fetchOne('SELECT user_id FROM auth_token where id = :id', ['id' => $token->getId()]);

        static::assertSame($this->user->getId(), $userId);
        static::assertSame($this->user->getId(), $token->getUser()->getId());
    }

    public function testUpdateWritesUser(): void
    {
        $importUser = new ImportUser();
        $importUser->id = uniqid('', true);
        $importUser->roles = [];
        $importUser->email = 'test@example.com';
        $importUser->displayName = 'Test';
        $importUser->fullName = 'Full Name';

        $user = $this->userService->updateUser($importUser);

        static::assertSame($importUser->id, $user->getExternalId());
        static::assertSame(['ROLE_USER'], $user->getRoles());
        static::assertSame($importUser->email, $user->getEmail());
        static::assertSame($importUser->displayName, $user->getName());
        static::assertSame($importUser->fullName, $user->getFullName());
    }

    public function testUpdateCreatesNotExisting(): void
    {
        $importUser = new ImportUser();
        $importUser->id = uniqid('', true);
        $importUser->roles = [];
        $importUser->email = 'test@example.com';
        $importUser->displayName = 'Test';
        $importUser->fullName = 'Full Name';

        $this->userService->updateUser($importUser);

        $user = $this->connection->fetchAssociative('SELECT * from user where external_id = :externalId', ['externalId' => $importUser->id]);

        static::assertNotFalse($user, 'User was not created');
        static::assertSame($importUser->id, $user['external_id']);
        static::assertSame($importUser->fullName, $user['full_name']);
        static::assertSame($importUser->displayName, $user['name']);
        static::assertSame($importUser->email, $user['email']);
        static::assertSame($importUser->roles, json_decode($user['roles'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function testUpdateSyncExistingUser(): void
    {
        $importUser = new ImportUser();
        $importUser->id = $this->user->getExternalId();
        $importUser->roles = [];
        $importUser->email = 'test@example.com';
        $importUser->displayName = 'Test';
        $importUser->fullName = 'Full Name';

        $this->userService->updateUser($importUser);

        $user = $this->connection->fetchAssociative('SELECT * from user where external_id = :externalId', ['externalId' => $importUser->id]);

        static::assertNotFalse($user, 'User was not created');
        static::assertSame($importUser->id, $user['external_id']);
        static::assertSame($importUser->fullName, $user['full_name']);
        static::assertSame($importUser->displayName, $user['name']);
        static::assertSame($importUser->email, $user['email']);
        static::assertSame($importUser->roles, json_decode($user['roles'], true, 512, JSON_THROW_ON_ERROR));
    }
}
