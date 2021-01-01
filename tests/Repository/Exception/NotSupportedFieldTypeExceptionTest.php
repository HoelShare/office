<?php declare(strict_types=1);

namespace App\Tests\Repository\Exception;

use App\Repository\Exception\NotSupportedFieldTypeException;
use PHPUnit\Framework\TestCase;

class NotSupportedFieldTypeExceptionTest extends TestCase
{
    public function testGetErrorsReturnsType(): void
    {
        $errors = (new NotSupportedFieldTypeException('type', 'class'))->getErrors();
        static::assertArrayHasKey('type', $errors);
    }

    public function testGetType(): void
    {
        $error = (new NotSupportedFieldTypeException('type', 'class'));
        static::assertSame('type', $error->getType());
    }

    public function testGetClass(): void
    {
        $error = (new NotSupportedFieldTypeException('type', 'class'));
        static::assertSame('class', $error->getObjectClass());
    }
}
