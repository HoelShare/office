<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
                $this->setValue($setterName, $object, $value);
            }
        }
    }

    private function isFkField(int|string $key): bool
    {
        return str_ends_with($key, 'Id');
    }

    private function mapFkValue(string $propertyName, $object, mixed $value): array
    {
        $setterName = 'set' . ucfirst($propertyName);
        $refectionClass = new ReflectionClass($object);
        $property = $refectionClass->getProperty($propertyName);

        $class = (string)$property->getType();
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
        $method = new \ReflectionMethod($object, $methodName);
        if ($method->getNumberOfRequiredParameters() !== 1) {
            throw new BadRequestHttpException();
        }

        $parameterClass = $this->removeNullable((string)$method->getParameters()[0]->getType());

        if (is_a($value, $parameterClass, true)) {
            $object->$methodName($value);
            return;
        }

        switch ($parameterClass) {
            case 'string':
            case 'int':
                break;
            case \DateTimeImmutable::class:
                $originalValue = $value;
                $value = \DateTimeImmutable::createFromFormat(DATE_ATOM, $value);
                if ($value === false) {
                    throw new BadRequestHttpException(
                        sprintf('%s not in Format %s', $originalValue, DATE_ATOM)
                    );
                }
                break;
            default:
                throw new BadRequestHttpException(
                    sprintf('Unsupported Class %s for method %s on %s', $parameterClass, $methodName, $object::class)
                );
        }

        $object->$methodName($value);
    }
}
