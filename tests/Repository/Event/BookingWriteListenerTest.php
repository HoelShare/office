<?php
declare(strict_types=1);

namespace App\Tests\Repository\Event;

use App\Entity\Booking;
use App\Repository\Event\BookingWriteListener;
use App\Repository\Event\WriteEvent;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\IntegrationTestBehaviour;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class BookingWriteListenerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use DemodataTrait;

    private BookingWriteListener $writeListener;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->writeListener = $this->getContainer()->get(BookingWriteListener::class);
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->addCommonData();
    }

    public function testIgnoresOthersThanBookings(): void
    {
        $security = $this->createMock(Security::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $listener = new BookingWriteListener($security, $em);
        $security->expects(static::never())->method(static::anything());
        $em->expects(static::never())->method(static::anything());

        $event = new WriteEvent('foo', null, []);
        $listener->writeBooking($event);
    }

    public function testListensToWriteEvents(): void
    {
        static::assertArrayHasKey(
            WriteEvent::class,
            BookingWriteListener::getSubscribedEvents()
        );
    }

    public function testFromDateAfterUntilDate(): void
    {
        $object = new Booking();
        $object->setFromDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));
        $object->setUntilDay(new DateTimeImmutable());

        $event = new WriteEvent(Booking::class, $object, []);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('From >= Until');
        $this->writeListener->writeBooking($event);
    }

    public function testCheckSetsUser(): void
    {
        $this->authorizeUser($this->user);
        $object = new Booking();
        $object->setFromDay(new DateTimeImmutable());
        $object->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));

        $event = new WriteEvent(Booking::class, $object, []);
        $this->writeListener->writeBooking($event);

        static::assertSame($this->user, $object->getUser());
    }

    public function testCheckUpdatesUser(): void
    {
        $this->authorizeUser($this->user);
        $object = new Booking();
        $object->setFromDay(new DateTimeImmutable());
        $object->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));
        $object->setUser($this->addUser());

        $event = new WriteEvent(Booking::class, $object, []);
        $this->writeListener->writeBooking($event);

        static::assertSame($this->user, $object->getUser());
    }

    public function testPrivilegedUserCanWriteOthers(): void
    {
        $this->authorizeUser($this->adminUser);
        $object = new Booking();
        $object->setFromDay(new DateTimeImmutable());
        $object->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));
        $object->setUser($this->user);

        $event = new WriteEvent(Booking::class, $object, []);
        $this->writeListener->writeBooking($event);

        static::assertSame($this->user, $object->getUser());
    }

    public function testPrivilegedUserCanWriteOwn(): void
    {
        $this->authorizeUser($this->adminUser);
        $object = new Booking();
        $object->setFromDay(new DateTimeImmutable());
        $object->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));
        $object->setUser($this->adminUser);

        $event = new WriteEvent(Booking::class, $object, []);
        $this->writeListener->writeBooking($event);

        static::assertSame($this->adminUser, $object->getUser());
    }

    public function testPrivilegedUserFallbackOwn(): void
    {
        $this->authorizeUser($this->adminUser);
        $object = new Booking();
        $object->setFromDay(new DateTimeImmutable());
        $object->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT1M')));

        $event = new WriteEvent(Booking::class, $object, []);
        $this->writeListener->writeBooking($event);

        static::assertSame($this->adminUser, $object->getUser());
    }

    public function testCheckBlockedEndsWhileBooking(): void
    {
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            new DateTimeImmutable(),
            (new DateTimeImmutable())->add(new DateInterval('PT8H'))
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay((new DateTimeImmutable())->sub(new DateInterval('PT4H')));
        $booking->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT4H')));

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Already taken');

        $this->writeListener->writeBooking($event);
    }

    public function testCheckBlockedStartsWhileBooking(): void
    {
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            new DateTimeImmutable(),
            (new DateTimeImmutable())->add(new DateInterval('PT8H'))
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay((new DateTimeImmutable())->add(new DateInterval('PT4H')));
        $booking->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT12H')));

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Already taken');

        $this->writeListener->writeBooking($event);
    }

    public function testCheckBlockedBookingInNewBooking(): void
    {
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            new DateTimeImmutable(),
            (new DateTimeImmutable())->add(new DateInterval('PT8H'))
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay((new DateTimeImmutable())->sub(new DateInterval('PT4H')));
        $booking->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT12H')));

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Already taken');

        $this->writeListener->writeBooking($event);
    }

    public function testCheckBlockedNewBookingInBooking(): void
    {
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            new DateTimeImmutable(),
            (new DateTimeImmutable())->add(new DateInterval('PT8H'))
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay((new DateTimeImmutable())->add(new DateInterval('PT4H')));
        $booking->setUntilDay((new DateTimeImmutable())->add(new DateInterval('PT5H')));

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Already taken');

        $this->writeListener->writeBooking($event);
    }

    public function testCheckBlockedNewBookingEndsOnBookingStart(): void
    {
        $startDate = new DateTimeImmutable();
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            $startDate,
            $startDate->add(new DateInterval('PT8H'))
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay($startDate->sub(new DateInterval('PT4H')));
        $booking->setUntilDay($startDate);

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->writeListener->writeBooking($event);
    }

    public function testCheckBlockedNewBookingStartsOnBookingEnd(): void
    {
        $startDate = new DateTimeImmutable();
        $seat = $this->addSeat();
        $this->addBooking(
            $this->user,
            $seat,
            $startDate->sub(new DateInterval('PT8H')),
            $startDate,
        );
        $this->getEntityManager()->flush();

        $booking = new Booking();
        $booking->setSeat($seat);
        $booking->setFromDay($startDate);
        $booking->setUntilDay($startDate->add(new DateInterval('PT4H')));

        $event = new WriteEvent(Booking::class, $booking, []);

        $this->writeListener->writeBooking($event);
    }
}
