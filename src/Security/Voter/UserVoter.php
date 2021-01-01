<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use function in_array;

class UserVoter extends Voter
{
    public function __construct(
        private Security $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User;
    }

    /**
     * @param User $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        if (in_array($attribute,
            [
                VoterAttributes::VOTE_CREATE,
                VoterAttributes::VOTE_UPDATE,
                VoterAttributes::VOTE_DELETE,
            ], true)) {
            return false;
        }

        $authedUser = $token->getUser();

        return ($authedUser !== null && $subject->getUsername() === $authedUser->getUsername())
            || $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }
}
