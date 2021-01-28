<?php
declare(strict_types=1);

namespace App\Repository;

use App\Repository\Exception\NotSupportedFieldTypeException;
use App\Repository\Exception\ValueNullException;
use App\Repository\Exception\WrongDateFormatException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use function boolval;

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
                if (method_exists($object, $setterName)) {
                    $this->setValue($setterName, $object, $value);
                }

                $propertyName = mb_substr($key, 0, -2);
                if (!property_exists($object, $propertyName)) {
                    continue;
                }

                [$setterName, $value] = $this->mapFkValue($propertyName, $object, $value);
            }

            if (method_exists($object, $setterName)) {
                try {
                    $this->setValue($setterName, $object, $value);
                } catch (NotSupportedFieldTypeException $exception) {
                    // NTH
                }
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
        $class = $this->removeNullable($class);

        $value = $this->entityManager->getReference($class, $value);

        return [$setterName, $value];
    }

    private function isNullableType(string $class): bool
    {
        return str_starts_with($class, '?');
    }

    private function removeNullable(string $class): string
    {
        if ($this->isNullableType($class)) {
            $class = mb_substr($class, 1);
        }

        return $class;
    }

    private function setValue(string $methodName, $object, mixed $value): void
    {
        $method = new ReflectionMethod($object, $methodName);
        if ($method->getNumberOfRequiredParameters() !== 1) {
            throw new RuntimeException();
        }
        $type = $method->getParameters()[0]->getType();

        if ($value === null && $type->allowsNull()) {
            $object->$methodName($value);

            return;
        }
        if ($value === null) {
            throw new ValueNullException();
        }

        $parameterClass = $this->removeNullable((string) $type);

        if (is_a($value, $parameterClass, true)) {
            $object->$methodName($value);

            return;
        }

        $value = $this->mapField($parameterClass, $value, $object);

        $object->$methodName($value);
    }

    private function mapField(string $parameterClass, mixed $value, $object): mixed
    {
        switch ($parameterClass) {
            case 'string':
                return (string) $value;
            case 'int':
                return (int) $value;
            case DateTimeImmutable::class:
                $originalValue = $value;
                $value = DateTimeImmutable::createFromFormat(DATE_ATOM, $value);
                if ($value === false) {
                    throw new WrongDateFormatException($originalValue, DATE_ATOM);
                }

                return $value;
            case 'bool':
                return boolval($value);
            default:
                throw new NotSupportedFieldTypeException(
                    $parameterClass,
                    $object::class,
                );
        }
    }
}
