<?php
declare(strict_types=1);

namespace App\Repository;

use App\Common\HydrateEvent;
use App\Repository\CriteriaBuilder\CriteriaBuilder;
use App\Repository\Event\UpdateEvent;
use App\Repository\Event\WriteEvent;
use App\Repository\Exception\NotAllowedException;
use App\Repository\Exception\ValidationException;
use App\Request\RepositoryContext;
use App\Security\Voter\VoterAttributes;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            throw new NotAllowedException('Not Allowed');
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
                throw new NotAllowedException('Not Allowed');
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
            ->isGranted(VoterAttributes::VOTE_CREATE, $object)) {
            throw new NotAllowedException('Not Allowed');
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
            throw new NotFoundHttpException();
        }

        unset($data['id']);
        $this->entityAssigner->assignData($object, $data);

        if (!$this->authorizationChecker
            ->isGranted(VoterAttributes::VOTE_UPDATE, $object)) {
            throw new NotAllowedException('Not Allowed');
        }

        $this->eventDispatcher->dispatch(new UpdateEvent($object::class, $object, $data));

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function delete(RepositoryContext $context, string $entity, string $id): void
    {
        $object = $this->get($entity, $id, $context);

        if (!$object) {
            throw new NotFoundHttpException();
        }

        if (!$this->authorizationChecker
                ->isGranted(VoterAttributes::VOTE_DELETE, $object)) {
            throw new NotAllowedException();
        }

        try {
            $this->entityManager->remove($object);
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new NotAllowedException();
        }
    }

    private function buildQuery(string $class, RepositoryContext $context): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from($class, CriteriaBuilder::SELECT_ALIAS);

        foreach ($this->criteriaFactory->build($class, $context, $queryBuilder) as $comparison) {
            $queryBuilder->andWhere($comparison);
        }

        $queryBuilder->select(CriteriaBuilder::SELECT_ALIAS);
        if ($context->getOrderBy() !== null) {
            $order = $context->getOrderBy();
            if (!str_contains($order, '.')) {
                $order = sprintf('%s.%s', CriteriaBuilder::SELECT_ALIAS, $order);
            }

            $queryBuilder->addOrderBy($order, $context->getOrderDirection());
        }

        $queryBuilder->setMaxResults($context->getLimit());
        $queryBuilder->setFirstResult($context->getOffset());

        return $queryBuilder;
    }
}
