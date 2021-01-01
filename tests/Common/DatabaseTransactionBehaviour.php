<?php
declare(strict_types=1);

namespace App\Tests\Common;

use Doctrine\DBAL\Connection;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DatabaseTransactionBehaviour
{
    public static $lastTestCase;

    /**
     * @before
     */
    public function startTransactionBefore(): void
    {
        static::assertNull(
            static::$lastTestCase,
            'The previous test case\'s transaction was not closed properly.
            This may affect following Tests in an unpredictable manner!
            Previous Test case: ' . (new ReflectionClass($this))->getName() . '::' . static::$lastTestCase
        );

        $this->getContainer()
            ->get(Connection::class)
            ->beginTransaction();

        static::$lastTestCase = $this->getName();
    }

    /**
     * @after
     */
    public function stopTransactionAfter(): void
    {
        $this->getContainer()->get('doctrine')->resetManager();

        /** @var Connection $connection */
        $connection = $this->getContainer()
            ->get(Connection::class);

        static::assertSame(
            1,
            $connection->getTransactionNestingLevel(),
            'Too many Nesting Levels.
            Probably one transaction was not closed properly.
            This may affect following Tests in an unpredictable manner!
            Current nesting level: "' . $connection->getTransactionNestingLevel() . '".'
        );

        $connection->rollBack();

        if (static::$lastTestCase === $this->getName()) {
            static::$lastTestCase = null;
        }
    }

    abstract protected function getContainer(): ContainerInterface;

    abstract protected static function assertSame($expected, $actual, string $message = ''): void;

    abstract protected static function assertNull($actual, string $message = ''): void;

    abstract protected function getName(): string;
}
