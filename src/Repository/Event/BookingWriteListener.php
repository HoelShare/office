<?php declare(strict_types=1);

namespace App\Repository\Event;

use App\Entity\Booking;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class BookingWriteListener implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WriteEvent::class => 'writeBooking',
        ];
    }

    public function writeBooking(WriteEvent $event): void
    {
        if ($event->getClass() !== Booking::class) {
            return;
        }

        /** @var Booking $booking */
        $booking = $event->getObject();

        $this->checkDate($booking);
        $this->checkUser($booking);
        $this->checkBlocked($booking);
    }

    private function checkDate(Booking $booking): void
    {
        if ($booking->getFromDay() < $booking->getUntilDay()) {
            return;
        }

        throw new BadRequestHttpException('From >= Until');
    }

    private function checkUser(Booking $booking): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')
            && $booking->getUser() !== null) {

            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $booking->setUser($user);
    }

    private function checkBlocked(Booking $booking): void
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->from(Booking::class, 'b')->select('b');
        $query->where($query->expr()->eq('b.seat', ':seat'))
            ->andWhere($query->expr()->orX(
                $query->expr()->andX(
                    $query->expr()->gt(':from', 'b.fromDay'),
                    $query->expr()->lt(':from', 'b.untilDay'),
                ),
                $query->expr()->andX(
                    $query->expr()->gt(':until', 'b.fromDay'),
                    $query->expr()->lt(':until', 'b.untilDay'),
                ),
                $query->expr()->andX(
                    $query->expr()->gt('b.fromDay', ':from'),
                    $query->expr()->lt('b.fromDay', ':until'),
                ),
                $query->expr()->andX(
                    $query->expr()->gt('b.untilDay', ':from'),
                    $query->expr()->lt('b.untilDay', ':until'),
                ),
            ))
            ->setParameter('seat', $booking->getSeat())
            ->setParameter('from', $booking->getFromDay())
            ->setParameter('until', $booking->getUntilDay());

        if (!$query->getQuery()->execute()) {
            return;
        }

        throw new BadRequestHttpException('Already taken');
    }
}
