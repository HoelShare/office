<?php
declare(strict_types=1);

namespace App\Security\Voter;

final class VoterAttributes
{
    public const VOTE_CREATE = 'create';
    public const VOTE_UPDATE = 'update';
    public const VOTE_READ = 'read';
    public const VOTE_DELETE = 'delete';
}
