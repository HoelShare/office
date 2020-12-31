<?php declare(strict_types=1);

namespace App\DBAL;

final class BookingEnumType extends EnumType
{
    protected string $name = 'enumbooking';
    protected array $values = ['whole day', 'morning', 'afternoon'];
}
