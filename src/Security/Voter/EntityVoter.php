<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Asset;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\Seat;
use App\Entity\SeatAsset;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntityVoter extends Voter
{
    private const SUPPORTED_ENTITIES = [
        Asset::class,
        Building::class,
        Floor::class,
        Seat::class,
        SeatAsset::class,
    ];

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        foreach (self::SUPPORTED_ENTITIES as $supportedEntity) {
            if (is_a($subject, $supportedEntity, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Floor $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($attribute === VoterAttributes::VOTE_READ) {
            return true;
        }

        return $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }
}
