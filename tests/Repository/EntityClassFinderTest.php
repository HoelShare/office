<?php
declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Asset;
use App\Entity\AuthToken;
use App\Entity\Booking;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\Seat;
use App\Entity\SeatAsset;
use App\Entity\User;
use App\Entity\Webhook;
use App\Repository\EntityClassFinder;
use App\Tests\Common\KernelTestBehaviour;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityClassFinderTest extends TestCase
{
    use KernelTestBehaviour;

    private EntityClassFinder $classFinder;

    protected function setUp(): void
    {
        $this->classFinder = $this->getContainer()->get(EntityClassFinder::class);
    }

    public function provideEntityClasses(): iterable
    {
        yield ['booking', Booking::class];
        yield ['building', Building::class];
        yield ['floor', Floor::class];
        yield ['authToken', AuthToken::class];
        yield ['asset', Asset::class];
        yield ['seat', Seat::class];
        yield ['seatasset', SeatAsset::class];
        yield ['user', User::class];
        yield ['webhook', Webhook::class];
    }

    /**
     * @dataProvider provideEntityClasses
     */
    public function testFindEntities(string $entityName, string $expectedClass): void
    {
        static::assertSame($expectedClass, $this->classFinder->findClass($entityName));
    }

    public function testNotExistingEntity(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->classFinder->findClass('foobar');
    }
}
