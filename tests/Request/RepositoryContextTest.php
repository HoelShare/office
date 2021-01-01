<?php declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\RepositoryContext;
use PHPUnit\Framework\TestCase;

class RepositoryContextTest extends TestCase
{
    public function testCreateWithNullLimitSetsDefault(): void
    {
        $context = new RepositoryContext(limit: null);
        static::assertSame(10, $context->getLimit());
    }

    public function testCreateWithNullOffsetSetsDefault(): void
    {
        $context = new RepositoryContext(offset: null);
        static::assertSame(0, $context->getOffset());
    }
}
