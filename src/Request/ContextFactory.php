<?php
declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ContextFactory
{
    public function create(Request $request, ?UserInterface $user): RepositoryContext
    {
        return new RepositoryContext(
            orderBy: $request->get('orderBy'),
            limit: (int) $request->get('limit', 10),
            offset: (int) $request->get('offset', 0),
            user: $user,
        );
    }
}
