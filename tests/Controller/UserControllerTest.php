<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use App\Tests\Common\WebTestBehaviour;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use WebTestBehaviour;
    use DemodataTrait;

    protected function setUp(): void
    {
        $this->addCommonData();
    }

    public function testMeReturnsUserInfo(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/user/me',
            server: ['HTTP_auth-token' => $this->user->getLdapTokens()->first()->getToken()]
        );

        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        static::assertSame(200, $client->getResponse()->getStatusCode());
        static::assertArrayHasKey('user', $jsonResponse);
        static::assertSame($this->user->jsonSerialize(), $jsonResponse['user']);
    }
}
