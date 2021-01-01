<?php
declare(strict_types=1);

namespace App\Tests\Repository\CriteriaBuilder;

use App\Repository\CriteriaBuilder\CriteriaBuilder;
use App\Repository\CriteriaBuilder\EntityCriteriaBuilder;
use App\Request\ContextFactory;
use App\Request\FilterTypes;
use App\Request\RepositoryContext;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function count;

class EntityCriteriaBuilderTest extends TestCase
{
    private function buildContext(array $conditions): RepositoryContext
    {
        $factory = new ContextFactory();
        $closure = function () use ($conditions) {
            return $this->extractCondition($conditions);
        };
        $whereCondition = $closure->call($factory);

        return new RepositoryContext(where: $whereCondition);
    }

    public function testEmptyCondition(): void
    {
        $criteriaBuilder = new EntityCriteriaBuilder();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method(static::anything());
        iterator_to_array($criteriaBuilder->build($this->buildContext([]), $queryBuilder));
    }

    public function testSingleCondition(): void
    {
        $criteriaBuilder = new EntityCriteriaBuilder();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $comparison = $this->createMock(Comparison::class);
        $expr = $this->createMock(Expr::class);
        $expr->expects(static::once())
            ->method('literal')
            ->with('foo')
            ->willReturn('literal');

        $expr2 = $this->createMock(Expr::class);
        $expr2->expects(static::once())
            ->method('eq')
            ->with(CriteriaBuilder::SELECT_ALIAS . '.name', 'literal')
            ->willReturn($comparison);

        $queryBuilder->expects(static::exactly(2))
            ->method('expr')
            ->willReturnOnConsecutiveCalls($expr, $expr2);

        $context = $this->buildContext(['name' => 'foo']);
        foreach ($criteriaBuilder->build($context, $queryBuilder) as $result) {
            static::assertSame($comparison, $result);
        }
    }

    public function testMultipleConditions(): void
    {
        $criteriaBuilder = new EntityCriteriaBuilder();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $condition = ['name' => 'foo', 'type' => 'bar', 'foo' => 42, 'unique' => uniqid('', true)];
        $context = $this->buildContext($condition);

        $expressions = [];
        $comparisons = [];

        foreach ($condition as $key => $value) {
            $comparison = $this->createMock(Comparison::class);
            $comparisons[] = $comparison;
            $expr = $this->createMock(Expr::class);
            $expr->expects(static::once())
                ->method('literal')
                ->with($value)
                ->willReturn($value);

            $expr2 = $this->createMock(Expr::class);
            $expr2->expects(static::once())
                ->method('eq')
                ->with(CriteriaBuilder::SELECT_ALIAS . '.' . $key, $value)
                ->willReturn($comparison);

            $expressions[] = $expr;
            $expressions[] = $expr2;
        }

        $queryBuilder->expects(static::exactly(count($expressions)))
            ->method('expr')
            ->willReturnOnConsecutiveCalls(...$expressions);

        $i = 0;
        foreach ($criteriaBuilder->build($context, $queryBuilder) as $result) {
            static::assertSame($comparisons[$i++], $result);
        }
    }

    public function testSingleConditionNotEquals(): void
    {
        $criteriaBuilder = new EntityCriteriaBuilder();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $comparison = $this->createMock(Comparison::class);
        $expr = $this->createMock(Expr::class);
        $expr->expects(static::once())
            ->method('literal')
            ->with('foo')
            ->willReturn('literal');

        $expr2 = $this->createMock(Expr::class);
        $expr2->expects(static::once())
            ->method('neq')
            ->with(CriteriaBuilder::SELECT_ALIAS . '.name', 'literal')
            ->willReturn($comparison);

        $queryBuilder->expects(static::exactly(2))
            ->method('expr')
            ->willReturnOnConsecutiveCalls($expr, $expr2);

        $context = $this->buildContext(['name' => ['type' => FilterTypes::NOT_EQUALS, 'value' => 'foo']]);
        foreach ($criteriaBuilder->build($context, $queryBuilder) as $result) {
            static::assertSame($comparison, $result);
        }
    }

    public function testSingleConditionNotSupported(): void
    {
        $criteriaBuilder = new EntityCriteriaBuilder();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $expr = $this->createMock(Expr::class);
        $expr->expects(static::once())
            ->method('literal')
            ->with('foo')
            ->willReturn('literal');

        $queryBuilder->expects(static::once())
            ->method('expr')
            ->willReturn($expr);

        $context = $this->buildContext(['name' => 'foo']);
        $where = $context->getWhere();
        $where['name']['type'] = 'not supported';

        $context = new RepositoryContext(where: $where);
        $this->expectException(BadRequestHttpException::class);
        iterator_to_array($criteriaBuilder->build($context, $queryBuilder));
    }
}
