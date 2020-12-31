<?php
declare(strict_types=1);

namespace App\Repository\CriteriaBuilder;

use App\Entity\User;
use App\Request\RepositoryContext;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserCriteriaBuilder implements CriteriaBuilder
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function supports(string $className, RepositoryContext $context): bool
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return false;
        }

        return is_a($className, User::class, true);
    }

    /**
     * @return iterable|Comparison[]
     */
    public function build(RepositoryContext $context, QueryBuilder $queryBuilder): iterable
    {
        $user = $context->getUser();
        if (!$user instanceof User) {
            return;
        }

        yield $queryBuilder->expr()->eq('e.id', $user->getId());
    }
}
