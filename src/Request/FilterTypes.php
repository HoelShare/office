<?php
declare(strict_types=1);

namespace App\Request;

final class FilterTypes
{
    public const EQUALS = 'eq';
    public const NOT_EQUALS = 'neq';
    public const GREATER_THAN = 'gt';
    public const GREATER_THAN_EQUALS = 'gte';
    public const LESS_THAN = 'lt';
    public const LESS_THAN_EQUALS = 'lte';
}
