<?php
declare(strict_types=1);

namespace App\Repository;

use App\Request\RepositoryContext;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;

class CriteriaFactory
{
    /**
     * @param iterable|CriteriaBuilder[] $builders
     */
    public function __construct(
        private iterable $builders,
    ) {
    }

    /**
     * @return iterable|Comparison[]
     */
    public function build(string $class, RepositoryContext $context, QueryBuilder $queryBuilder): iterable
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($class, $context)) {
                yield from $builder->build($context, $queryBuilder);
            }
        }
    }
}
