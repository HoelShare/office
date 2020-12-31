<?php
declare(strict_types=1);

namespace App\Repository\CriteriaBuilder;

use App\Request\FilterTypes;
use App\Request\RepositoryContext;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EntityCriteriaBuilder implements CriteriaBuilder
{
    public function supports(string $className, RepositoryContext $context): bool
    {
        return $context->getWhere() !== null;
    }

    /**
     * @return iterable|Comparison[]
     */
    public function build(RepositoryContext $context, QueryBuilder $queryBuilder): iterable
    {
        foreach ($context->getWhere() as $key => $value) {
            if (!str_contains($key, '.')) {
                $key = sprintf('%s.%s', CriteriaBuilder::SELECT_ALIAS, $key);
            }
            yield $this->map($key, $value, $queryBuilder);
        }
    }

    private function map(string $field, array $data, QueryBuilder $queryBuilder): Comparison
    {
        $value = $queryBuilder->expr()->literal($data['value']);
        $type = $data['type'];
        return match ($type) {
            FilterTypes::EQUALS,
            FilterTypes::NOT_EQUALS,
            FilterTypes::GREATER_THAN_EQUALS,
            FilterTypes::GREATER_THAN,
            FilterTypes::LESS_THAN_EQUALS,
            FilterTypes::LESS_THAN => $queryBuilder->expr()->$type($field, $value),
            default => throw new BadRequestHttpException(),
        };
    }
}
