<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

class EntityAssigner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function assignData($object, array $data): void
    {
        foreach ($data as $key => $value) {
            $setterName = 'set' . ucfirst($key);

            if ($this->isFkField($key)) {
                $propertyName = mb_substr($key, 0, -2);
                if (!property_exists($object, $propertyName)) {
                    continue;
                }

                [$setterName, $value] = $this->mapFkValue($propertyName, $object, $value);
            }

            if (method_exists($object, $setterName)) {
                $object->$setterName($value);
            }
        }
    }

    private function isFkField(int | string $key): bool
    {
        return str_ends_with($key, 'Id');
    }

    private function mapFkValue(string $propertyName, $object, mixed $value): array
    {
        $setterName = 'set' . ucfirst($propertyName);
        $refectionClass = new ReflectionClass($object);
        $property = $refectionClass->getProperty($propertyName);

        $class = (string) $property->getType();

        if ($this->isNullableType($class)) {
            $class = $this->removeNullable($class);
        }

        $value = $this->entityManager->getReference($class, $value);

        return [$setterName, $value];
    }

    private function isNullableType(string $class): bool
    {
        return str_starts_with($class, '?');
    }

    private function removeNullable(string $class): string
    {
        $class = mb_substr($class, 1);

        return $class;
    }
}
