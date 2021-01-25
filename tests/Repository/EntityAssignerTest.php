<?php
declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Asset;
use App\Entity\Booking;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\User;
use App\Repository\EntityAssigner;
use App\Repository\Exception\ValueNullException;
use App\Repository\Exception\WrongDateFormatException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EntityAssignerTest extends TestCase
{
    private EntityAssigner $assigner;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->assigner = new EntityAssigner($this->entityManager);
    }

    public function testAssignData(): void
    {
        $user = new User();
        $data = [
            'id' => 1,
            'fullName' => 'Tester',
        ];

        $this->assigner->assignData($user, $data);

        static::assertSame(1, $user->getId());
        static::assertSame('Tester', $user->getFullName());
    }

    public function testAssignDataIgnoresNotFoundColumns(): void
    {
        $user = new User();
        $data = [
            'foobar' => 'foo',
        ];

        $this->assigner->assignData($user, $data);

        static::assertArrayNotHasKey('foobar', $user->jsonSerialize());
    }

    public function testWriteFkField(): void
    {
        $floor = new Floor();
        $data = [
            'name' => 'Main',
            'buildingId' => 1,
        ];

        $mockReference = new Building();
        $mockReference->setId(1);
        $this->entityManager->expects(static::once())->method('getReference')->willReturn($mockReference);

        $this->assigner->assignData($floor, $data);

        static::assertSame($mockReference, $floor->getBuilding());
        static::assertSame('Main', $floor->getName());
    }

    public function testCanWriteSimpleTypes(): void
    {
        $floor = new Floor();
        $data = [
            'name' => 'Main',
            'number' => 1,
        ];

        $this->assigner->assignData($floor, $data);
        static::assertSame(1, $floor->getNumber());
        static::assertSame('Main', $floor->getName());
    }

    public function testConvertStringsToDate(): void
    {
        $date = DateTimeImmutable::createFromFormat(DATE_ATOM, (new DateTimeImmutable())
            ->format(DATE_ATOM));
        $booking = new Booking();
        $data = [
            'fromDay' => $date->format(DATE_ATOM),
        ];

        $this->assigner->assignData($booking, $data);

        static::assertEquals($date, $booking->getFromDay());
    }

    public function testConvertStringsToDateWillThrowExceptionOnWrongFormat(): void
    {
        $date = DateTimeImmutable::createFromFormat(DATE_ATOM, (new DateTimeImmutable())
            ->format(DATE_ATOM));
        $value = $date->format(DATE_RFC3339_EXTENDED);
        $booking = new Booking();
        $data = [
            'fromDay' => $value,
        ];

        try {
            $this->assigner->assignData($booking, $data);
            static::fail('Exception was not thrown');
        } catch (WrongDateFormatException $dateFormatException) {
            static::assertInstanceOf(WrongDateFormatException::class, $dateFormatException);
            static::assertSame([
                'expectedFormat' => DATE_ATOM,
                'currentValue' => $value,
            ], $dateFormatException->getErrors());
        }
    }

    public function testConvertsSimpleType(): void
    {
        $floor = new Floor();
        $data = [
            'number' => '1',
        ];

        $this->assigner->assignData($floor, $data);
        static::assertSame(1, $floor->getNumber());
    }

    public function testWriteNotSupportedComplexType(): void
    {
        $floor = $this->createMock(Floor::class);
        $data = [
            'seats' => 'collection of seats',
        ];

        $floor->expects(static::never())->method('setSeats');

        $this->assigner->assignData($floor, $data);
    }

    public function testWriteComplexType(): void
    {
        $arrayCollection = new ArrayCollection();
        $arrayCollection->add('foobar');
        $floor = new Floor();
        $data = [
            'seats' => $arrayCollection,
        ];

        $this->assigner->assignData($floor, $data);

        static::assertSame($arrayCollection, $floor->getSeats());
    }

    public function testWriteNull(): void
    {
        $floor = new Floor();
        $floor->setName('Floor');
        $data = [
            'name' => null,
        ];

        $this->assigner->assignData($floor, $data);

        static::assertNull($floor->getName());
    }

    public function testWriteNullToNonNullableField(): void
    {
        $asset = new Asset();
        $asset->setName('Asset');
        $data = [
            'name' => null,
        ];

        $this->expectException(ValueNullException::class);
        $this->assigner->assignData($asset, $data);
    }

    public function testWriteNotExistingProperty(): void
    {
        $floor = new Floor();
        $data = [
            'fkId' => 1,
        ];

        $this->assigner->assignData($floor, $data);
        static::assertArrayNotHasKey('fkId', $floor->jsonSerialize());
    }

    public function testWriteToFieldWithMoreThan1RequiredField(): void
    {
        $c = new class() {
            public function setFoo($foo, $bar): void
            {
            }
        };

        $data = [
            'foo' => 'bar',
        ];

        $this->expectException(RuntimeException::class);
        $this->assigner->assignData($c, $data);
    }
}
