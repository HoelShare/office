<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use App\Tests\Common\WebTestBehaviour;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use WebTestBehaviour;
    use DemodataTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->addCommonData();
    }

    public function testListActionsCallable(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/asset',
            server: ['HTTP_auth-token' => $this->getUserToken()]
        );

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testListActionReturnsListWithEntityNameAsKey(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/asset',
            server: ['HTTP_auth-token' => $this->getUserToken()]
        );
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('asset', $jsonResponse);
        static::assertIsArray($jsonResponse['asset']);

        $count = (int)$this->connection->fetchOne('SELECT count(*) from asset');
        static::assertCount($count, $jsonResponse['asset']);
    }

    public function testListCanBeFiltered(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/asset',
            server: ['HTTP_auth-token' => $this->getUserToken(), 'HTTP_content-type' => 'application/json'],
            content: json_encode(['where' => ['type' => 'Monitor']], JSON_THROW_ON_ERROR),
        );
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('asset', $jsonResponse);
        static::assertIsArray($jsonResponse['asset']);

        $count = (int)$this->connection->fetchOne('SELECT count(*) from asset where type = :type', ['type' => 'Monitor']);
        static::assertCount($count, $jsonResponse['asset']);
        static::assertGreaterThan(0, $count);
    }

    public function testListCanBeFilteredWithQueryArguments(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/asset?where={"type": "Monitor"}',
            server: ['HTTP_auth-token' => $this->getUserToken()],
        );
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('asset', $jsonResponse);
        static::assertIsArray($jsonResponse['asset']);

        $count = (int)$this->connection->fetchOne('SELECT count(*) from asset where type = :type', ['type' => 'Monitor']);
        static::assertCount($count, $jsonResponse['asset']);
        static::assertGreaterThan(0, $count);
    }


    public function testListCanBeFilteredWithMultiple(): void
    {
        $client = $this->getClient();
        $client->request('GET',
            '/api/asset',
            parameters: ['where' => ['type' => 'OS', 'name' => ['type' => 'neq', 'value' => 'Windows']]],
            server: ['HTTP_auth-token' => $this->getUserToken(), 'HTTP_content-type' => 'application/json'],
        );
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('asset', $jsonResponse);
        static::assertIsArray($jsonResponse['asset']);

        $count = (int)$this->connection->fetchOne('SELECT count(*) from asset where type = :type and name <> :name', ['type' => 'os', 'name' => 'Windows']);
        static::assertCount($count, $jsonResponse['asset']);
        static::assertGreaterThan(0, $count);
    }

    public function testDetailNotFound(): void
    {
        $this->getEntityManager()->flush();

        $client = $this->getClient();
        $client->request('GET',
            sprintf('/api/asset/%s', 1000000),
            server: ['HTTP_auth-token' => $this->getUserToken()]
        );

        static::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testDetailShowsInfo(): void
    {
        $asset = $this->addAsset('Foo', 'Bar');
        $this->getEntityManager()->flush();

        $client = $this->getClient();
        $client->request('GET',
            sprintf('/api/asset/%s', $asset->getId()),
            server: ['HTTP_auth-token' => $this->getUserToken()]
        );
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertSame($asset->jsonSerialize(), $jsonResponse);
    }

    public function testCreateAsUser(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/asset',
            server: ['HTTP_auth-token' => $this->getUserToken()],
            content: json_encode([], JSON_THROW_ON_ERROR));

        static::assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testCreateWithInvalidData(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST, '/api/asset',
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)],
            content: json_encode([], JSON_THROW_ON_ERROR));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        unset($jsonResponse['errors']['meta']);

        static::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        static::assertSame(['errors' => [
            'type' => ['This value should not be blank.'],
            'name' => ['This value should not be blank.'],
        ]], $jsonResponse);
    }

    public function testCreateAsset(): void
    {
        $client = $this->getClient();

        $countBefore = (int)$this->connection->fetchOne('SELECT count(*) FROM asset');

        $client->request(Request::METHOD_POST, '/api/asset',
            parameters: ['name' => 'foo', 'type' => 'bar'],
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)],
        );

        $countAfter = (int)$this->connection->fetchOne('SELECT count(*) FROM asset');

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        static::assertSame($countBefore + 1, $countAfter);

        $result = $this->connection->fetchAssociative('SELECT * FROM asset order by id desc limit 1');

        static::assertSame('foo', $result['name']);
        static::assertSame('bar', $result['type']);
    }


    public function testCreateUser(): void
    {
        $client = $this->getClient();

        $client->request(Request::METHOD_POST, '/api/user',
            parameters: ['name' => 'foo',],
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)],
        );

        static::assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testUpdateNotExisting(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_PATCH,
            sprintf('/api/asset/%s', 1000000),
            parameters: ['name' => 'New Name'],
            server: ['HTTP_auth-token' => $this->getUserToken()]
        );

        static::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testUpdateSingleField(): void
    {
        $asset = $this->addAsset('Name', 'Type');
        $this->getEntityManager()->flush();

        $client = $this->getClient();
        $client->request(Request::METHOD_PATCH,
            sprintf('/api/asset/%s', $asset->getId()),
            parameters: ['name' => 'New Name'],
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)]
        );

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $data = $this->connection->fetchAssociative('SELECT * FROM asset where id = :id', ['id' => $asset->getId()]);

        static::assertSame('New Name', $data['name']);
        static::assertSame('Type', $data['type']);
    }

    public function testUpdateMultipleFields(): void
    {
        $asset = $this->addAsset('Name', 'Type');
        $this->getEntityManager()->flush();

        $client = $this->getClient();
        $client->request(Request::METHOD_PATCH,
            sprintf('/api/asset/%s', $asset->getId()),
            parameters: ['name' => 'New Name', 'type' => 'New Type'],
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)]
        );

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $data = $this->connection->fetchAssociative('SELECT * FROM asset where id = :id', ['id' => $asset->getId()]);

        static::assertSame('New Name', $data['name']);
        static::assertSame('New Type', $data['type']);
    }

    public function testUpdateSetNull(): void
    {
        $building = $this->addBuilding(name: 'Building', city: 'City');
        $this->getEntityManager()->flush();

        $client = $this->getClient();
        $client->request(Request::METHOD_PATCH,
            sprintf('/api/building/%s', $building->getId()),
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser), 'HTTP_content-type' => 'application/json'],
            content: json_encode(['city' => null], JSON_THROW_ON_ERROR),
        );

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $data = $this->connection->fetchAssociative('SELECT * FROM building where id = :id', ['id' => $building->getId()]);

        static::assertSame('Building', $data['name']);
        static::assertNull($data['city']);
    }

    public function testDeleteNotExisting(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_DELETE,
            sprintf('/api/asset/%s', 1000000),
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)]
        );

        static::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testDelete(): void
    {
        $asset = $this->addAsset('name', 'type');
        $this->getEntityManager()->flush();
        $id = $asset->getId();

        $client = $this->getClient();
        $client->request(Request::METHOD_DELETE,
            sprintf('/api/asset/%s', $id),
            server: ['HTTP_auth-token' => $this->getUserToken($this->adminUser)]
        );

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $result = $this->connection->fetchOne('SELECT * FROM asset where id = :id', ['id' => $id]);
        static::assertFalse($result, 'Entity was not deleted');
    }

    private function getUserToken(?User $user = null): string
    {
        if ($user === null) {
            $user = $this->user;
        }

        return $user->getLdapTokens()->first()->getToken();
    }
}
