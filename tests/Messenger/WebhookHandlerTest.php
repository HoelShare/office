<?php
declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Entity\Asset;
use App\Messenger\CreateMessage;
use App\Messenger\DeleteMessage;
use App\Messenger\UpdateMessage;
use App\Messenger\WebhookHandler;
use App\Messenger\WebhookMessageInterface;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookHandlerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use DemodataTrait;

    private WebhookHandler $handler;

    private Connection $connection;

    private MockObject |

HttpClientInterface $client;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->handler = new WebhookHandler($this->client, $this->connection);
        $this->addCommonData();
    }

    public function provideSystemMessages(): iterable
    {
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        yield [new CreateMessage('entity', $asset, null)];
        yield [new UpdateMessage('entity', $asset, null, ['name' => 'foo'])];
        yield [new DeleteMessage('entity', '1', null)];
    }

    public function provideAdminUserMessages(): iterable
    {
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        yield new CreateMessage('entity', $asset, $this->adminUser);
        yield new UpdateMessage('entity', $asset, $this->adminUser, ['name' => 'foo']);
        yield new DeleteMessage('entity', '1', $this->adminUser);
    }

    public function provideUserMessages(): iterable
    {
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        yield new CreateMessage('entity', $asset, $this->user);
        yield new UpdateMessage('entity', $asset, $this->user, ['name' => 'foo']);
        yield new DeleteMessage('entity', '1', $this->user);
    }

    /**
     * @dataProvider provideSystemMessages
     */
    public function testSendsSystemMessages(WebhookMessageInterface $message): void
    {
        $this->addWebhook('http://url', null);

        $this->client->expects(static::once())
            ->method('request')
            ->with('POST', 'http://url',
                [
                    'json' => $message->toArray(),
                ]
            );

        $this->handler->__invoke($message);
    }

    /**
     * @dataProvider provideSystemMessages
     */
    public function testSendsMultipleMessages(WebhookMessageInterface $message): void
    {
        $this->addWebhook('http://url', null);
        $this->addWebhook('http://url2', null);
        $this->addWebhook('http://url3', null);

        $this->client->expects(static::exactly(3))
            ->method('request')
            ->withConsecutive([
                'POST', 'http://url', [
                    'json' => $message->toArray(),
                ],
            ], [
                'POST', 'http://url2', [
                    'json' => $message->toArray(),
                ],
            ], [
                'POST', 'http://url3', [
                    'json' => $message->toArray(),
                ],
            ],
            );

        $this->handler->__invoke($message);
    }

    public function testSendsCreateMessageToUserWebhook(): void
    {
        $this->addWebhook('http://user.local', $this->user->getId());
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        $message = new CreateMessage('entity', $asset, $this->user);
        $this->client->expects(static::once())
            ->method('request')
            ->with('POST', 'http://user.local',
                [
                    'json' => $message->toArray(),
                ]
            );

        $this->handler->__invoke($message);
    }

    public function testSendsUpdateMessageToUserWebhook(): void
    {
        $this->addWebhook('http://user.local', $this->user->getId());
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        $message = new UpdateMessage('entity', $asset, $this->user, ['name' => 'foo']);
        $this->client->expects(static::once())
            ->method('request')
            ->with('POST', 'http://user.local',
                [
                    'json' => $message->toArray(),
                ]
            );

        $this->handler->__invoke($message);
    }

    public function testSendsDeleteMessageToUserWebhook(): void
    {
        $this->addWebhook('http://user.local', $this->user->getId());
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');
        $message = new DeleteMessage('entity', '1', $this->user);
        $this->client->expects(static::once())
            ->method('request')
            ->with('POST', 'http://user.local',
                [
                    'json' => $message->toArray(),
                ]
            );

        $this->handler->__invoke($message);
    }

    public function testSendsMessageToUserAndSystemWebhook(): void
    {
        $this->addWebhook('http://user.local', $this->user->getId());
        $this->addWebhook('http://system', null);
        $asset = new Asset();
        $asset->setName('foo');
        $asset->setType('bar');

        $message = new CreateMessage('entity', $asset, $this->user);

        $this->client->expects(static::exactly(2))
            ->method('request')
            ->withConsecutive([
                'POST', 'http://user.local',
                [
                    'json' => $message->toArray(),
                ],
            ], [
                'POST', 'http://system',
                [
                    'json' => $message->toArray(),
                ],
            ],
            );

        $this->handler->__invoke($message);
    }

    public function testIgnoresOtherUser(): void
    {
        $this->addWebhook('http://user.local', $this->user->getId());
        foreach ($this->provideAdminUserMessages() as $message) {
            $this->client->expects(static::never())->method(static::anything());

            $this->handler->__invoke($message);
        }
    }

    private function addWebhook(string $url, ?int $userId, bool $active = true): void
    {
        $this->connection->insert(
            'webhook',
            [
                'user_id' => $userId,
                'webhook_url' => $url,
                'active' => (int) $active,
            ],
        );
    }
}
