<?php
declare(strict_types=1);

namespace App\Controller;

use App\Messenger\CreateMessage;
use App\Messenger\DeleteMessage;
use App\Messenger\UpdateMessage;
use App\Repository\EntityRepository;
use App\Request\ContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/{entity}", name="apiController", requirements={"entity"="^(?!logout)[A-za-z\-]+$"})
 */
class ApiController extends AbstractController
{
    public function __construct(
        private ContextFactory $contextFactory,
        private EntityRepository $entityRepository
    ) {
    }

    /**
     * @Route(path="", name="list", methods={"GET"})
     */
    public function listAction(Request $request, string $entity): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $rows = $this->entityRepository->read($entity, $context);

        return new JsonResponse([$entity => $rows]);
    }

    /**
     * @Route(path="/{id}", name="detail", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function detailAction(Request $request, string $entity, string $id): JsonResponse
    {
        $context = $this->contextFactory->create($request, $this->getUser());

        $detail = $this->entityRepository->get($entity, $id, $context);

        if ($detail === null) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($detail);
    }

    /**
     * @Route(path="", name="create", methods={"POST"})
     */
    public function createAction(
        Request $request,
        MessageBusInterface $bus,
        string $entity
    ): JsonResponse {
        $object = $this->entityRepository->write($entity, $request->request->all());

        $bus->dispatch(new CreateMessage($entity, $object, $this->getUser()));
        return new JsonResponse($object);
    }

    /**
     * @Route(path="/{id}", name="update", methods={"PATCH"}, requirements={"id"="\d+"})
     */
    public function updateAction(
        Request $request,
        MessageBusInterface $bus,
        string $entity,
        string $id,
    ): JsonResponse {
        $context = $this->contextFactory->create($request, $this->getUser());
        $data = $request->request->all();
        $object = $this->entityRepository->update($context, $entity, $id, $data);

        $bus->dispatch(new UpdateMessage($entity, $object, $this->getUser(), $data));
        return new JsonResponse($object);
    }

    /**
     * @Route(path="/{id}", name="delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function deleteAction(
        Request $request,
        MessageBusInterface $bus,
        string $entity,
        string $id
    ): JsonResponse {
        $context = $this->contextFactory->create($request, $this->getUser());

        $this->entityRepository->delete($context, $entity, $id);

        $bus->dispatch(new DeleteMessage($entity, $id, $this->getUser()));
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
