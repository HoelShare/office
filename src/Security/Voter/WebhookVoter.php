<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\Webhook;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class WebhookVoter extends Voter
{
    public function __construct(
        private Security $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Webhook;
    }

    /**
     * @param Webhook $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $authedUser */
        $authedUser = $token->getUser();

        return $subject->getUserId() === null
            || $subject->getUserId() === $authedUser->getId()
            || $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }
}
