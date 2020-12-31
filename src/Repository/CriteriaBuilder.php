<?php
declare(strict_types=1);

namespace App\Repository;

use App\Request\RepositoryContext;
use Doctrine\ORM\QueryBuilder;

interface CriteriaBuilder
{
    public function supports(string $className, RepositoryContext $context): bool;

    public function build(RepositoryContext $context, QueryBuilder $queryBuilder): iterable;
}
