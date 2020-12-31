<?php
declare(strict_types=1);

namespace App\Repository;

use App\Repository\CriteriaBuilder\CriteriaBuilder;
use App\Repository\Event\UpdateEvent;
use App\Repository\Event\WriteEvent;
use App\Repository\Exception\NotAllowedException;
use App\Repository\Exception\ValidationException;
use App\Request\RepositoryContext;
use App\Security\Voter\VoterAttributes;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CriteriaFactory $criteriaFactory,
        private AuthorizationCheckerInterface $authorizationChecker,
        private ValidatorInterface $validator,
        private EntityClassFinder $classFinder,
        private EntityAssigner $entityAssigner,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function get(string $entity,
                        string $id,
                        RepositoryContext $context
    ): ?object {
        $class = $this->classFinder->findClass($entity);
        $queryBuilder = $this->buildQuery($class, $context);
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq(CriteriaBuilder::SELECT_ALIAS . '.id', $id)
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
        $class = $this->classFinder->findClass($entity);
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
        $class = $this->classFinder->findClass($entity);
        $object = new $class();

        unset($data['id']);
        $this->entityAssigner->assignData($object, $data);

        if (!$this->authorizationChecker
            ->isGranted(VoterAttributes::VOTE_CREATE, $entity)) {
            throw new Exception('Not Allowed');
        }

        $validations = $this->validator->validate($object);
        if ($validations->count()) {
            throw new ValidationException($validations);
        }

        $this->eventDispatcher->dispatch(new WriteEvent($class, $object, $data));

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
        $this->entityAssigner->assignData($object, $data);

        if (!$this->authorizationChecker
            ->isGranted(VoterAttributes::VOTE_UPDATE, $entity)) {
            throw new Exception('Not Allowed');
        }

        $this->eventDispatcher->dispatch(new UpdateEvent($object::class, $object, $data));

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

    private function buildQuery(string $class, RepositoryContext $context): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from($class, CriteriaBuilder::SELECT_ALIAS);

        foreach ($this->criteriaFactory->build($class, $context, $queryBuilder) as $comparison) {
            $queryBuilder->andWhere($comparison);
        }

        $queryBuilder->select(CriteriaBuilder::SELECT_ALIAS);
        if ($context->getOrderBy()) {
            $queryBuilder->addOrderBy($context->getOrderBy());
        }

        $queryBuilder->setMaxResults($context->getLimit());
        $queryBuilder->setFirstResult($context->getOffset());

        return $queryBuilder;
    }
}
