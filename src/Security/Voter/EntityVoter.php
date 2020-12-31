<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Floor;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class EntityVoter extends Voter
{
    public function __construct(
        private Security $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject !== null;
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
