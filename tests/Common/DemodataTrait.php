<?php declare(strict_types=1);

namespace App\Tests\Common;

use App\Entity\Asset;
use App\Entity\Booking;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\LdapToken;
use App\Entity\Seat;
use App\Entity\SeatAsset;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DemodataTrait
{
    abstract protected function getContainer(): ContainerInterface;

    protected ?User $adminUser;
    protected ?User $user;

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get(EntityManagerInterface::class);
    }

    protected function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    protected function addCommonData(): void
    {
        $this->adminUser = $this->addAdminUser();
        $this->user = $this->addUser();

        $monitor34 = $this->addAsset('34\'', 'Monitor');
        $dualMonitor = $this->addAsset('2x 17\'', 'Monitor');

        $mac = $this->addAsset('Mac', 'OS');
        $linux = $this->addAsset('Linux', 'OS');
        $windows = $this->addAsset('Windows', 'OS');

        $mainBuilding = $this->addBuilding(name: 'Main');

        $floor = $this->addFloor(name: '1st Floor', building: $mainBuilding);
        $seat = $this->addSeat($floor, number: 1, locationX: 20, locationY: 20);
        $this->addSeatAsset($seat, $monitor34);
        $this->addSeatAsset($seat, $mac);
        $this->addSeatAsset($seat, $linux);
        $this->addSeatAsset($seat, $windows);
        $this->addBooking(
            $this->user,
            $seat,
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T08:00:00+00:00'),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T17:00:00+00:00'),
        );

        $seat = $this->addSeat($floor, number: 5, locationX: 5, locationY: 24.5);
        $this->addSeatAsset($seat, $monitor34);
        $this->addSeatAsset($seat, $mac);
        $this->addSeatAsset($seat, $linux);
        $this->addSeatAsset($seat, $windows);
        $this->addBooking(
            $this->user,
            $seat,
            fromDay: DateTimeImmutable::createFromFormat(DATE_ATOM, '2020-12-31T08:00:00+00:00'),
        );
        $this->addBooking(
            $this->addUser(),
            $seat,
            fromDay: DateTimeImmutable::createFromFormat(DATE_ATOM, '2020-12-30T08:00:00+00:00'),
        );
        $this->addBooking(
            $this->user,
            $seat,
            fromDay: DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T08:00:00+00:00'),
        );

        $floor = $this->addFloor(name: '2nd Floor', building: $mainBuilding);
        $seat = $this->addSeat($floor, number: 1, locationX: 20, locationY: 20);
        $this->addSeatAsset($seat, $dualMonitor);
        $this->addSeatAsset($seat, $linux);
        $this->addSeatAsset($seat, $windows);
        $this->addBooking(
            $this->adminUser,
            $seat,
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T08:00:00+00:00'),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T17:00:00+00:00'),
        );

        $seat = $this->addSeat($floor, number: 2, locationX: 50, locationY: 30);
        $this->addSeatAsset($seat, $dualMonitor);
        $this->addSeatAsset($seat, $mac);
        $this->addBooking(
            $this->user,
            $seat,
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T08:00:00+00:00'),
            DateTimeImmutable::createFromFormat(DATE_ATOM, '2021-01-01T17:00:00+00:00'),
        );

        $seat = $this->addSeat($floor, number: 42, locationX: 100.4, locationY: 30.2);
        $this->addSeatAsset($seat, $monitor34);
        $this->addSeatAsset($seat, $mac);
        $this->addSeatAsset($seat, $linux);

        $secondBuilding = $this->addBuilding(name: 'Second');
        $floor = $this->addFloor(name: 'Main Floor', building: $secondBuilding);
        $seat = $this->addSeat($floor, number: 6);
        $this->addSeatAsset($seat, $monitor34);
        $this->addSeatAsset($seat, $mac);
        $this->addSeatAsset($seat, $linux);

        $this->getEntityManager()->flush();
    }

    protected function addUser(
        ?int $id = null,
        ?string $ldapId = null,
        ?string $name = null,
        ?array $roles = ['ROLE_USER'],
    ): User {
        $user = new User();
        if ($id) {
            $user->setId($id);
        }
        if ($ldapId === null) {
            $ldapId = uniqid('', true);
        }

        $user->setName($name);
        $user->setRoles($roles);
        $user->setLdapId($ldapId);

        $ldapToken = new LdapToken();
        $ldapToken->setToken(uniqid('', true));
        $user->addLdapToken($ldapToken);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->persist($ldapToken);

        return $user;
    }

    protected function addAdminUser(
        ?int $id = null,
        ?string $name = null,
        ?array $roles = ['ROLE_ADMIN'],
    ): User {
        return $this->addUser(id: $id, name: $name, roles: $roles);
    }

    protected function addAsset(
        string $name,
        string $type,
        ?int $id = null,
        ?Collection $seatAssets = null,
    ): Asset {
        $asset = new Asset();
        if ($id !== null) {
            $asset->setId($id);
        }

        $asset->setName($name);
        $asset->setType($type);
        if ($seatAssets !== null) {
            $asset->setSeatAssets($seatAssets);
        }

        $this->getEntityManager()->persist($asset);

        return $asset;
    }

    protected function addBooking(
        User $user,
        Seat $seat,
        ?DateTimeImmutable $fromDay = null,
        ?DateTimeImmutable $untilDay = null,
    ): Booking {
        if ($fromDay === null) {
            $fromDay = new DateTimeImmutable();
        }
        if ($untilDay === null) {
            $untilDay = $fromDay->add(new DateInterval('PT9H'));
        }

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setSeat($seat);
        $booking->setFromDay($fromDay);
        $booking->setUntilDay($untilDay);

        $this->getEntityManager()->persist($booking);

        return $booking;
    }

    protected function addBuilding(?int $id = null, string $name = '', ?string $city = null): Building
    {
        $building = new Building();
        if ($id !== null) {
            $building->setId($id);
        }

        $building->setName($name);
        $building->setCity($city);

        $this->getEntityManager()->persist($building);

        return $building;
    }

    protected function addFloor(
        ?int $id = null,
        ?string $name = null,
        ?string $floorPath = null,
        ?Building $building = null,
        ?int $number = null,
        ?Collection $seats = null,
    ): Floor {
        $floor = new Floor();
        if ($id !== null) {
            $floor->setId($id);
        }

        $floor->setName($name);
        $floor->setFloorPath($floorPath);

        if ($building === null) {
            $building = $this->addBuilding();
        }

        $floor->setBuilding($building);
        $floor->setNumber($number);
        if ($seats !== null) {
            $floor->setSeats($seats);
        }

        $this->getEntityManager()->persist($floor);

        return $floor;
    }

    protected function addSeat(
        ?Floor $floor = null,
        int $number = 0,
        float $locationX = 0,
        float $locationY = 0,
        ?int $id = null,
    ): Seat {
        $seat = new Seat();
        if ($id !== null) {
            $seat->setId($id);
        }

        if ($floor === null) {
            $floor = $this->addFloor();
        }

        $seat->setFloor($floor);
        $seat->setNumber($number);
        $seat->setLocationX($locationX);
        $seat->setLocationY($locationY);

        $this->getEntityManager()->persist($seat);

        return $seat;
    }

    protected function addSeatAsset(
        Seat $seat,
        Asset $asset,
        int $order = 0,
        ?int $id = null,
    ): SeatAsset {
        $seatAsset = new SeatAsset();
        if ($id !== null) {
            $seatAsset->setId($id);
        }

        $seatAsset->setPriority($order);
        $seatAsset->setSeat($seat);
        $seatAsset->setAsset($asset);

        $this->getEntityManager()->persist($seatAsset);

        return $seatAsset;
    }

    protected function addLdapToken(User $user): LdapToken
    {
        $ldapToken = new LdapToken();
        $ldapToken->setToken(uniqid('', true));
        $ldapToken->setUser($user);

        $this->getEntityManager()->persist($ldapToken);

        return $ldapToken;
    }
}
