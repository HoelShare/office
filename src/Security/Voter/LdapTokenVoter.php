<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\LdapToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LdapTokenVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof LdapToken;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return false;
    }
}
