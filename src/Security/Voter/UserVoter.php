<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    public function __construct(private Security $security)
    {
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
        $authedUser = $token->getUser();

        return $subject->getUsername() === $authedUser->getUsername()
            || $this->security->isGranted('ROLE_ADMIN');
    }
}
