<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

trait EntitySerializableTrait
{
    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);

        return array_filter($vars, static function ($value, $key) {
            return !str_starts_with($key, '_') && !$value instanceof Collection;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
