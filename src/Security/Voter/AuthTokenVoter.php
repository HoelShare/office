<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\AuthToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuthTokenVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof AuthToken;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return false;
    }
}
