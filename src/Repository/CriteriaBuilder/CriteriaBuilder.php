<?php
declare(strict_types=1);

namespace App\Repository\CriteriaBuilder;

use App\Request\RepositoryContext;
use Doctrine\ORM\QueryBuilder;

interface CriteriaBuilder
{
    public const SELECT_ALIAS = 'e';

    public function supports(string $className, RepositoryContext $context): bool;

    public function build(RepositoryContext $context, QueryBuilder $queryBuilder): iterable;
}
