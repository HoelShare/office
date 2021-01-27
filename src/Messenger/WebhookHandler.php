<?php declare(strict_types=1);

namespace App\Messenger;

use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookHandler implements MessageHandlerInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(WebhookMessageInterface $message)
    {
        $urls = $this->getWebhookUrls($message->getUserId());

        foreach ($urls as $url) {
            $this->sendWebhook($url, $message);
        }
    }

    private function getWebhookUrls(?int $userId): array
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select('webhook_url')
            ->from('webhook', 'w')
            ->where('w.active = 1 and w.user_id is null');

        if ($userId !== null) {
            $query->orWhere('w.active = 1 and w.user_id = :userId')
                ->setParameter('userId', $userId);
        }

        /** @var Statement $stmt */
        $stmt = $query->execute();

        return $stmt->fetchFirstColumn();
    }

    private function sendWebhook(string $url, WebhookMessageInterface $message): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            ['json' => $message->toArray()]
        );
    }
}