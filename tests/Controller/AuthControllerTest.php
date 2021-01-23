<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\AuthController;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use App\Tests\Common\WebTestBehaviour;
use App\User\UserService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use WebTestBehaviour;
    use DemodataTrait;

    protected function setUp(): void
    {
        $this->addCommonData();
    }

    public function testAuthenticate(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'Hubert J. Farnsworth',
            'password' => 'professor',
        ]);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAuthenticateFails(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'Hubert J. Farnsworth',
            'password' => 'test',
        ]);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthenticateWithoutUsername(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'password' => 'test',
        ]);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthenticateWithoutPassword(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'test',
        ]);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthenticateReturnsAuthKey(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'Hubert J. Farnsworth',
            'password' => 'professor',
        ]);

        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('authToken', $jsonResponse);
    }

    public function testAuthenticateReturnsUserInfo(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'Hubert J. Farnsworth',
            'password' => 'professor',
        ]);

        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('user', $jsonResponse);
    }

    public function testAuthenticateDifferentUser(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/login', [
            'username' => 'Philip J. Fry',
            'password' => 'fry',
        ]);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAuthFailedWithoutToken(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/user/me',
            server: ['HTTP_auth-token' => null]
        );

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthFailedWithInvalidToken(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/user/me',
            server: ['HTTP_auth-token' => uniqid('', true)]
        );

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testLoginRouteDoesNothing(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects(static::never())->method(static::anything());
        $controller = new AuthController($userService);
        $controller->loginAction();
    }

    public function testLogoutRemovesToken(): void
    {
        $connection = $this->getContainer()->get(Connection::class);
        $connection->executeQuery('INSERT INTO user(external_id, roles) values (:id, :roles)',
            ['id' => uniqid('', true), 'roles' => '["ROLE_USER"]',]);
        $userId = (int) $connection->lastInsertId();
        $token = uniqid('', true);
        $connection->executeQuery('INSERT INTO auth_token (user_id, token) values (:id, :token)',
            ['id' => $userId, 'token' => $token]);

        $client = $this->getClient();
        $client->request(Request::METHOD_GET,
            '/api/logout',
            server: ['HTTP_auth-token' => $token],
        );

        $loggedOut = (int)$connection->fetchOne('SELECT count(*) from auth_token where token = :token',
            ['token' => $token]);
        static::assertSame(0, $loggedOut);
    }
}
