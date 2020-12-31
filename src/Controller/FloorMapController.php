<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Floor;
use App\File\FileHandler;
use App\Repository\EntityRepository;
use App\Request\ContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FloorMapController extends AbstractController
{
    public function __construct(
        private EntityRepository $entityRepository,
        private ContextFactory $contextFactory,
        private FileHandler $fileHandler,
    ) {
    }

    /**
     * @Route("/api/floor/{id}/upload", name="uploadMap", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function uploadMap(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $files = $request->files->all();
        if (!$files) {
            throw new BadRequestHttpException('No File sent');
        }
        $context = $this->contextFactory->create($request, $this->getUser());

        /** @var Floor $detail */
        $detail = $this->entityRepository->get('floor', $id, $context);
        $this->fileHandler->uploadMap($detail, array_values($files)[0]);

        return new JsonResponse(['floor' => $detail]);
    }
}
