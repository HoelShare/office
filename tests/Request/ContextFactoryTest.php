<?php declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\ContextFactory;
use App\Request\FilterTypes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ContextFactoryTest extends TestCase
{
    public function testDescOrder(): void
    {
        $factory = new ContextFactory();
        $request = new Request(request: ['orderBy' => '-name']);
        $context = $factory->create($request, null);

        static::assertSame('DESC', $context->getOrderDirection());
        static::assertSame('name', $context->getOrderBy());
    }

    public function testExtractFromQuery(): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name' => 'foo']]);
        $context = $factory->create($request, null);

        static::assertSame(['name' => ['type' => FilterTypes::EQUALS, 'value' => 'foo']], $context->getWhere());
    }

    public function testWhereMissesFields(): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name', 'foo']]);

        $this->expectException(BadRequestHttpException::class);
        $factory->create($request, null);
    }

    public function testWhereMissesValue(): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name' => ['type' => FilterTypes::EQUALS]]]);

        $this->expectException(BadRequestHttpException::class);
        $factory->create($request, null);
    }

    public function testWhereMissesType(): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name' => ['value' => 'foo']]]);

        $this->expectException(BadRequestHttpException::class);
        $factory->create($request, null);
    }

    public function testWhereInvalidType(): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name' => ['type' => uniqid('', true), 'value' => 'foo']]]);

        $this->expectException(BadRequestHttpException::class);
        $factory->create($request, null);
    }

    /**
     * @dataProvider provideWhereTypes
     */
    public function testWhereTypes(string $type): void
    {
        $factory = new ContextFactory();
        $request = new Request(query: ['where' => ['name' => ['type' => $type, 'value' => 'foo']]]);

        $context = $factory->create($request, null);

        static::assertSame(['name' => ['type' => $type, 'value' => 'foo']], $context->getWhere());
    }

    public function provideWhereTypes(): iterable
    {
        yield [FilterTypes::EQUALS];
        yield [FilterTypes::NOT_EQUALS];
        yield [FilterTypes::LESS_THAN_EQUALS];
        yield [FilterTypes::LESS_THAN];
        yield [FilterTypes::GREATER_THAN_EQUALS];
        yield [FilterTypes::GREATER_THAN];
    }
}
