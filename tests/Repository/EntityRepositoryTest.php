<?php
declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Asset;
use App\Entity\Booking;
use App\Entity\Floor;
use App\Repository\EntityRepository;
use App\Repository\Event\UpdateEvent;
use App\Repository\Event\WriteEvent;
use App\Repository\Exception\NotAllowedException;
use App\Repository\Exception\ValidationException;
use App\Repository\Exception\ValueNullException;
use App\Request\RepositoryContext;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use function count;

class EntityRepositoryTest extends TestCase
{
    use IntegrationTestBehaviour;
    use DemodataTrait;

    private EntityRepository $entityRepository;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->entityRepository = $this->getContainer()->get(EntityRepository::class);
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->addCommonData();
    }

    public function testGetNotExisting(): void
    {
        $context = new RepositoryContext(user: $this->user);
        $entity = $this->entityRepository->get('floor', '-1', $context);

        static::assertNull($entity);
    }

    public function testGetExisting(): void
    {
        $context = new RepositoryContext();
        $id = $this->connection->fetchOne('SELECT ID FROM floor');
        $entity = $this->entityRepository->get('floor', $id, $context);

        static::assertNotNull($entity);
    }

    public function testGetExistingWithAcl(): void
    {
        $this->authorizeUser($this->user);
        $context = new RepositoryContext(user: $this->user);
        $entity = $this->entityRepository->get('user', (string) $this->adminUser->getId(), $context);
        static::assertNull($entity);
        $entity = $this->entityRepository->get('user', (string) $this->user->getId(), $context);
        static::assertNotNull($entity);

        $this->authorizeUser($this->adminUser);
        $context = new RepositoryContext(user: $this->adminUser);
        $entity = $this->entityRepository->get('user', (string) $this->adminUser->getId(), $context);
        static::assertNotNull($entity);
        $entity = $this->entityRepository->get('user', (string) $this->user->getId(), $context);
        static::assertNotNull($entity);
    }

    public function testGetNotAllowed(): void
    {
        $this->authorizeUser($this->adminUser);
        $context = new RepositoryContext();
        $token = $this->addAuthToken($this->adminUser);
        $this->getEntityManager()->flush();

        $this->expectException(NotAllowedException::class);
        $this->entityRepository->get('authToken', (string) $token->getId(), $context);
    }

    public function testGetNotExistingEntityClass(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->entityRepository->get('foobar', '1', new RepositoryContext());
    }

    public function testReadEntities(): void
    {
        $entities = $this->entityRepository->read('floor', new RepositoryContext());
        static::assertNotCount(0, $entities);

        foreach ($entities as $entity) {
            static::assertInstanceOf(Floor::class, $entity);
        }
    }

    public function testReadUserWithoutUserInContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No user set');
        $this->entityRepository->read('user', new RepositoryContext());
    }

    public function testReadFilteredEntities(): void
    {
        $this->authorizeUser($this->user);
        $context = new RepositoryContext(user: $this->user);
        $entities = $this->entityRepository->read('user', $context);
        $count = (int) $this->connection->fetchOne('SELECT count(*) FROM user');

        static::assertNotCount($count, $entities);
        static::assertLessThan($count, count($entities));

        $this->authorizeUser($this->adminUser);
        $context = new RepositoryContext(user: $this->adminUser);
        $entities = $this->entityRepository->read('user', $context);
        static::assertCount($count, $entities);
    }

    public function testReadNotAllowed(): void
    {
        $context = new RepositoryContext(user: $this->user);
        $this->addAuthToken($this->user);
        $this->getEntityManager()->flush();
        $this->expectException(NotAllowedException::class);
        $this->entityRepository->read('authToken', $context);
    }

    public function testReadOtherEntity(): void
    {
        $context = new RepositoryContext(orderBy: 'id', orderDirection: 'DESC');
        $bookings = $this->entityRepository->read('booking', $context);

        foreach ($bookings as $booking) {
            static::assertInstanceOf(Booking::class, $booking);
        }
    }

    public function testReadOrderBy(): void
    {
        $context = new RepositoryContext(orderBy: 'id', orderDirection: 'DESC');
        $entities = $this->entityRepository->read('floor', $context);

        $prevId = null;
        foreach ($entities as $entity) {
            if ($prevId !== null) {
                static::assertLessThan($prevId, $entity->getId());
            }
            $prevId = $entity->getId();
        }
    }

    public function testReadOrderByNumber(): void
    {
        $context = new RepositoryContext(orderBy: 'number', orderDirection: 'ASC');
        $entities = $this->entityRepository->read('floor', $context);

        $prevNumber = 0;
        foreach ($entities as $entity) {
            static::assertGreaterThanOrEqual($prevNumber, $entity->getNumber());
            $prevNumber = $entity->getNumber();
        }
    }

    public function testWriteDispatchesEvent(): void
    {
        $this->authorizeUser($this->adminUser);

        $data = ['name' => 'Foo', 'type' => 'bar'];

        $this->assertEvent(WriteEvent::class,
            fn () => $this->entityRepository->write(new RepositoryContext(), 'asset', $data),
            function ($event) use ($data): void {
                static::assertInstanceOf(WriteEvent::class, $event);
                static::assertSame(Asset::class, $event->getClass());
                static::assertInstanceOf(Asset::class, $event->getObject());
                static::assertSame($data, $event->getRawData());
            },
        );
    }

    public function testWriteNotAllowed(): void
    {
        $data = ['name' => 'Foo', 'type' => 'bar'];

        $this->expectException(NotAllowedException::class);
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);
    }

    public function testWriteNotAllowedByVoters(): void
    {
        $this->authorizeUser($this->user);
        $data = ['userId' => $this->adminUser->getId()];

        $this->expectException(NotAllowedException::class);
        $this->entityRepository->write(new RepositoryContext(), 'booking', $data);
    }

    public function testWriteChecksValidations(): void
    {
        $this->authorizeUser($this->adminUser);
        $data = ['type' => 'foo'];

        try {
            $this->entityRepository->write(new RepositoryContext(), 'asset', $data);
            static::fail('Exception was not thrown');
        } catch (ValidationException $exception) {
            static::assertSame(['name' => [
                'This value should not be blank.',
            ]], $exception->getErrors());
        }
    }

    public function testWriteAddsEntity(): void
    {
        $this->authorizeUser($this->adminUser);
        $countBefore = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM asset');

        $data = ['name' => 'Foo', 'type' => 'bar'];
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);

        $countAfter = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM asset');
        static::assertSame($countBefore + 1, $countAfter);
    }

    public function testWriteAssignsCorrectData(): void
    {
        $this->authorizeUser($this->adminUser);
        $data = ['name' => 'Foo', 'type' => 'bar'];
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);

        $result = $this->connection->fetchAssociative('SELECT * FROM asset order by ID desc');
        static::assertEquals('bar', $result['type']);
        static::assertEquals('Foo', $result['name']);

        $data = ['name' => 'name', 'type' => 'type'];
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);

        $result = $this->connection->fetchAssociative('SELECT * FROM asset order by ID desc');
        static::assertEquals('type', $result['type']);
        static::assertEquals('name', $result['name']);
    }

    public function testUpdateNotFound(): void
    {
        $id = (int) $this->connection->fetchOne('SELECT MAX(ID) FROM asset');
        $this->expectException(NotFoundHttpException::class);
        $this->entityRepository->update(new RepositoryContext(), 'asset', (string) ($id + 1), []);
    }

    public function testUpdateNotAllowed(): void
    {
        $id = $this->connection->fetchOne('SELECT MAX(ID) FROM asset');
        $this->expectException(NotAllowedException::class);
        $this->entityRepository->update(new RepositoryContext(), 'asset', $id, []);
    }

    public function testUpdateDispatchesEvent(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT MAX(ID) FROM asset');
        $data = ['name' => 'foobar'];
        $this->assertEvent(
            UpdateEvent::class,
            fn () => $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data),
            function ($event) use ($data): void {
                static::assertInstanceOf(UpdateEvent::class, $event);
                static::assertSame(Asset::class, $event->getClass());
                static::assertInstanceOf(Asset::class, $event->getObject());
                static::assertSame($data, $event->getRawData());
            },
        );
    }

    public function testUpdateUnsetsId(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT MAX(ID) FROM asset');
        $data = ['name' => 'foobar', 'id' => 4];
        $this->assertEvent(
            UpdateEvent::class,
            fn () => $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data),
            function ($event): void {
                static::assertArrayNotHasKey('id', $event->getRawData());
            },
        );
    }

    public function testUpdateChecksNullable(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT MAX(ID) FROM asset');
        $data = ['name' => null];

        try {
            $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);
            static::fail('Exception was not thrown');
        } catch (ValueNullException $exception) {
            static::assertArrayHasKey('error', $exception->getErrors());
        }
    }

    public function testUpdateUpdatesData(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT id FROM asset');
        $newName = uniqid('', true);
        $newType = uniqid('', true);
        $data = ['name' => $newName, 'type' => $newType];

        $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);
        $updated = $this->connection->fetchAssociative('SELECT * FROM asset where id = :id', ['id' => $id]);

        static::assertSame($newType, $updated['type']);
        static::assertSame($newName, $updated['name']);
    }

    public function testUpdateChecksValidations(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT id FROM asset');

        $data = ['name' => ''];

        try {
            $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);
            static::fail('Exception was not thrown');
        } catch (ValidationException $exception) {
            static::assertSame(['name' => [
                'This value should not be blank.',
            ]], $exception->getErrors());
        }
    }

    public function testDeleteNotAllowed(): void
    {
        $this->authorizeUser($this->user);
        $id = $this->connection->fetchOne('SELECT id FROM asset');
        $this->expectException(NotAllowedException::class);
        $this->entityRepository->delete(new RepositoryContext(), 'asset', $id);
    }

    public function testDeleteRemovesEntityWithFk(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT a.id FROM asset a inner join seat_asset sa on sa.asset_id = a.id');

        $this->expectException(NotAllowedException::class);
        $this->entityRepository->delete(new RepositoryContext(), 'asset', $id);
    }

    public function testDeleteRemovesEntity(): void
    {
        $this->authorizeUser($this->adminUser);
        $asset = $this->addAsset('asset', 'type');
        $this->getEntityManager()->flush();
        $id = $asset->getId();

        $this->entityRepository->delete(new RepositoryContext(), 'asset', (string) $id);

        $result = $this->connection->fetchOne('SELECT id FROM asset where id = :id', ['id' => $id]);
        static::assertFalse($result);
    }

    public function testCreateDispatchesMessage(): void
    {
        $this->authorizeUser($this->adminUser);
        $data = ['name' => 'Foo', 'type' => 'bar'];
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);
        $this->entityRepository->write(new RepositoryContext(), 'asset', $data);

        /** @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        static::assertCount(3, $transport->get());
    }

    public function testUpdateDispatchesMessage(): void
    {
        $this->authorizeUser($this->adminUser);
        $id = $this->connection->fetchOne('SELECT id FROM asset');
        $newName = uniqid('', true);
        $newType = uniqid('', true);
        $data = ['name' => $newName, 'type' => $newType];

        $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);
        $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);
        $this->entityRepository->update(new RepositoryContext(), 'asset', $id, $data);

        /** @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        static::assertCount(3, $transport->get());
    }

    public function testDeleteDispatchesMessage(): void
    {
        $this->authorizeUser($this->adminUser);
        $asset = $this->addAsset('asset', 'type');
        $this->getEntityManager()->flush();
        $id = $asset->getId();

        $this->entityRepository->delete(new RepositoryContext(), 'asset', (string) $id);

        /** @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        static::assertCount(1, $transport->get());
    }

    public function testWriteWebhookAsUser(): void
    {
        $this->authorizeUser($this->user);

        $data = ['webhookUrl' => 'url', 'active' => false];
        $webhook = $this->entityRepository->write(new RepositoryContext(), 'webhook', $data);
        $result = $this->connection->fetchAssociative('SELECT * FROM webhook where id = :id', ['id' => $webhook->getId()]);
        static::assertEquals('url', $result['webhook_url']);
        static::assertEquals($this->user->getId(), $result['user_id']);
        static::assertEquals(0, $result['active']);
    }

    public function testUserCannotWriteForOthers(): void
    {
        $this->authorizeUser($this->user);

        $data = ['webhookUrl' => 'url', 'userId' => $this->adminUser->getId()];
        $this->expectException(NotAllowedException::class);
        $this->entityRepository->write(new RepositoryContext(), 'webhook', $data);
    }

    public function testGetWebhookAsUser(): void
    {
        $this->authorizeUser($this->user);
        $this->connection->insert('webhook', ['webhook_url' => 'localhost']);

        $webhook = $this->entityRepository->get('webhook', $this->connection->lastInsertId(), new RepositoryContext(user: $this->user));

        static::assertNull($webhook);
    }

    public function testGetWebhookWithoutUserThrowsException(): void
    {
        $this->connection->insert('webhook', ['webhook_url' => 'localhost']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No user set');

        $this->entityRepository->get('webhook', $this->connection->lastInsertId(), new RepositoryContext());
    }

    public function testWriteWebhookAsAdminIsSystemWebhook(): void
    {
        $this->authorizeUser($this->adminUser);

        $data = ['webhookUrl' => 'url'];
        $webhook = $this->entityRepository->write(new RepositoryContext(), 'webhook', $data);
        $result = $this->connection->fetchAssociative('SELECT * FROM webhook where id = :id', ['id' => $webhook->getId()]);
        static::assertNull($result['user_id']);
    }

    public function testAdminCanWriteWebhookForOthers(): void
    {
        $this->authorizeUser($this->adminUser);

        $data = ['webhookUrl' => 'url', 'userId' => $this->user->getId()];
        $webhook = $this->entityRepository->write(new RepositoryContext(), 'webhook', $data);
        $result = $this->connection->fetchAssociative('SELECT * FROM webhook where id = :id', ['id' => $webhook->getId()]);
        static::assertEquals('url', $result['webhook_url']);
        static::assertEquals($this->user->getId(), $result['user_id']);
        static::assertEquals(1, $result['active']);
    }
}
