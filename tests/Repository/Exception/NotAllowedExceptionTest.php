<?php declare(strict_types=1);

namespace App\Tests\Repository\Exception;

use App\Repository\Exception\NotAllowedException;
use PHPUnit\Framework\TestCase;

class NotAllowedExceptionTest extends TestCase
{
    public function testGetErrors(): void
    {
        $error = new NotAllowedException();
        static::assertSame([], $error->getErrors());
    }
}
