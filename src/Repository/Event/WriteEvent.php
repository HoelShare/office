<?php
declare(strict_types=1);

namespace App\Repository\Event;

use Symfony\Contracts\EventDispatcher\Event;

class WriteEvent extends Event
{
    public function __construct(
        private string $class,
        private mixed $object,
        private array $rawData,
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getObject(): mixed
    {
        return $this->object;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }
}
