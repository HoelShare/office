<?php declare(strict_types=1);

namespace App\Repository\Event;

use App\Entity\Booking;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use function Doctrine\ORM\QueryBuilder;

class BookingWriteListener implements EventSubscriberInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
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

        if ($booking->getFromDay() > $booking->getUntilDay()) {
            throw new BadRequestHttpException('From > Until');
        }

        if (!$this->security->isGranted('ROLE_ADMIN')
            || $booking->getUser() === null) {
            /** @var User $user */
            $user = $this->security->getUser();
            $booking->setUser($user);
        }

        $query = $this->entityManager->createQueryBuilder();
        $query->from(Booking::class, 'b')->select('b');
        $query->where($query->expr()->eq('b.seat', ':seat'))
            ->andWhere($query->expr()->orX(
                $query->expr()->andX(
                    $query->expr()->gte(':from', 'b.fromDay'),
                    $query->expr()->lt(':from', 'b.untilDay'),
                ),
                $query->expr()->andX(
                    $query->expr()->gte(':until', 'b.fromDay'),
                    $query->expr()->lt(':until', 'b.untilDay'),
                ),
                $query->expr()->andX(
                    $query->expr()->gt('b.fromDay', ':from'),
                    $query->expr()->lte('b.fromDay', ':until'),
                ),
                $query->expr()->andX(
                    $query->expr()->gt('b.untilDay', ':from'),
                    $query->expr()->lte('b.untilDay', ':until'),
                ),
            ))
            ->setParameter('seat', $booking->getSeat())
            ->setParameter('from', $booking->getFromDay())
            ->setParameter('until', $booking->getUntilDay());

        if ($query->getQuery()->execute()) {
            throw new BadRequestHttpException('Already taken');
        }
    }
}
