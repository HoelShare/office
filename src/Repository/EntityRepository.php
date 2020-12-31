<?php
declare(strict_types=1);

namespace App\Repository;

use App\Repository\Exception\NotAllowedException;
use App\Repository\Exception\ValidationException;
use App\Request\RepositoryContext;
use App\Security\Voter\VoterAttributes;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityRepository
{
    public const SELECT_ALIAS = 'e';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CriteriaFactory $criteriaFactory,
        private AuthorizationCheckerInterface $authorizationChecker,
        private ValidatorInterface $validator,
    ) {
    }

    public function get(string $entity,
                        string $id,
                        RepositoryContext $context
    ): ?object {
        $class = $this->getClass($entity);
        $queryBuilder = $this->buildQuery($class, $context);
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq(self::SELECT_ALIAS . '.id', $id)
            );

        $result = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();

        if ($result && !$this->authorizationChecker
                ->isGranted(VoterAttributes::VOTE_READ, $result)) {
            throw new Exception('Not Allowed');
        }

        return $result;
    }

    public function read(string $entity, RepositoryContext $context): array
    {
        $class = $this->getClass($entity);
        $queryBuilder = $this->buildQuery($class, $context);

        $results = $queryBuilder->getQuery()->getResult();

        foreach ($results as $result) {
            if (!$this->authorizationChecker
                ->isGranted(VoterAttributes::VOTE_READ, $result)) {
                throw new Exception('Not Allowed');
            }
        }

        return $results;
    }

    public function write(string $entity, array $data): void
    {
        $class = $this->getClass($entity);
        $object = new $class();

        unset($data['id']);
        $this->assignData($object, $data);

        if (!$this->authorizationChecker
            ->isGranted(VoterAttributes::VOTE_CREATE, $entity)) {
            throw new Exception('Not Allowed');
        }

        $validations = $this->validator->validate($object);
        if ($validations->count()) {
            throw new ValidationException($validations);
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function update(RepositoryContext $context, string $entity, string $id, array $data): void
    {
        $object = $this->get($entity, $id, $context);

        if (!$object) {
            throw new Exception('Not Allowed');
        }

        unset($data['id']);
        $this->assignData($object, $data);

        if (!$this->authorizationChecker
            ->isGranted(VoterAttributes::VOTE_UPDATE, $entity)) {
            throw new Exception('Not Allowed');
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function delete(RepositoryContext $context, string $entity, string $id): void
    {
        $object = $this->get($entity, $id, $context);

        if (!$object || !$this->authorizationChecker
                ->isGranted(VoterAttributes::VOTE_DELETE, $entity)) {
            throw new NotAllowedException();
        }

        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    private function assignData($object, array $data): void
    {
        foreach ($data as $key => $value) {
            $setterName = 'set' . ucfirst($key);

            if (str_ends_with($key, 'Id')) {
                $propertyName = mb_substr($key, 0, -2);
                if (!property_exists($object, $propertyName)) {
                    continue;
                }

                $setterName = 'set' . ucfirst($propertyName);
                $refectionClass = new ReflectionClass($object);
                $property = $refectionClass->getProperty($propertyName);

                $class = (string) $property->getType();

                if (str_starts_with($class, '?')) {
                    $class = mb_substr($class, 1);
                }

                $value = $this->entityManager->getReference($class, $value);
            }

            if (method_exists($object, $setterName)) {
                $object->$setterName($value);
            }
        }
    }

    private function getClass(string $entity): string
    {
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $class = null;
        foreach ($metas as $meta) {
            $nameParts = explode('\\', $meta->getName());
            $name = array_pop($nameParts);

            if (mb_strtolower($name) !== mb_strtolower($entity)) {
                continue;
            }

            $class = $meta->getName();
        }

        if (!$class) {
            throw new NotFoundHttpException(sprintf('%s not found', $entity));
        }

        return $class;
    }

    private function buildQuery(string $class, RepositoryContext $context): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from($class, self::SELECT_ALIAS);

        foreach ($this->criteriaFactory->build($class, $context, $queryBuilder) as $comparison) {
            $queryBuilder->andWhere($comparison);
        }

        $queryBuilder->select(self::SELECT_ALIAS);
        if ($context->getOrderBy()) {
            $queryBuilder->addOrderBy($context->getOrderBy());
        }

        $queryBuilder->setMaxResults($context->getLimit());
        $queryBuilder->setFirstResult($context->getOffset());

        return $queryBuilder;
    }
}
