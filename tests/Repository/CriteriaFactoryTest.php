<?php
declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\CriteriaBuilder\CriteriaBuilder;
use App\Repository\CriteriaFactory;
use App\Request\RepositoryContext;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class CriteriaFactoryTest extends TestCase
{
    public function testCallsEveryBuilder(): void
    {
        $builders = [];
        for ($i = 0; $i < 10; $i++) {
            $builder = $this->createMock(CriteriaBuilder::class);
            $builder->expects(static::once())->method('supports')->willReturn(false);
            $builder->expects(static::never())->method('build');
            $builders[] = $builder;
        }

        $factory = new CriteriaFactory($builders);
        iterator_to_array($factory->build(self::class,
            $this->createMock(RepositoryContext::class),
            $this->createMock(QueryBuilder::class)
        ));
    }

    public function testCallsBuildIfSupported(): void
    {
        $builders = [];
        for ($i = 0; $i < 10; $i++) {
            $builder = $this->createMock(CriteriaBuilder::class);
            $builder->expects(static::once())->method('supports')->willReturn(true);
            $builder->expects(static::once())->method('build');
            $builders[] = $builder;
        }

        $factory = new CriteriaFactory($builders);
        iterator_to_array($factory->build(self::class,
            $this->createMock(RepositoryContext::class),
            $this->createMock(QueryBuilder::class)
        ));
    }

    public function testWillYieldFromBuild(): void
    {
        $builders = [];
        for ($i = 0; $i < 10; $i++) {
            $builder = $this->createMock(CriteriaBuilder::class);
            $builder->expects(static::once())->method('supports')->willReturn(true);
            $builder->expects(static::once())->method('build')->willReturnCallback(fn () => yield $i);
            $builders[] = $builder;
        }

        $factory = new CriteriaFactory($builders);
        $i = 0;
        foreach ($factory->build(self::class,
            $this->createMock(RepositoryContext::class),
            $this->createMock(QueryBuilder::class)
        ) as $comparison) {
            static::assertSame($i++, $comparison);
        }

        static::assertGreaterThan(0, $i);
    }
}
