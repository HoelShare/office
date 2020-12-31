<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\EntityRepository;
use App\Request\ContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api", name="apiController")
 */
class ApiController extends AbstractController
{
    public function __construct(
        private ContextFactory $contextFactory,
        private EntityRepository $entityRepository
    ) {
    }

    /**
     * @Route(path="/{entity}", name="list", methods={"GET"})
     */
    public function listAction(Request $request, ContextFactory $contextFactory, string $entity): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $rows = $this->entityRepository->read($entity, $context);

        return new JsonResponse([$entity => $rows]);
    }

    /**
     * @Route(path="/{entity}/{id}", name="detail", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function detailAction(Request $request, string $entity, string $id): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $detail = $this->entityRepository->get($entity, $id, $context);

        return new JsonResponse($detail);
    }

    /**
     * @Route(path="/{entity}", name="create", methods={"POST"})
     */
    public function createAction(Request $request, string $entity): JsonResponse
    {
        $this->entityRepository->write($entity, $request->request->all());

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="/{entity}/{id}", name="update", methods={"PATCH"}, requirements={"id"="\d+"})
     */
    public function updateAction(Request $request, string $entity, string $id): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $this->entityRepository->update($context, $entity, $id, $request->request->all());

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="/{entity}/{id}", name="update", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function deleteAction(Request $request, string $entity, string $id): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $this->entityRepository->delete($context, $entity, $id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
