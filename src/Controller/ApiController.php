<?php
declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api", name="apiController")
 */
class ApiController extends AbstractController
{
    /**
     * @Route(path="/{entity}", name="index", methods={"GET"})
     */
    public function indexAction(EntityManagerInterface $entityManager, Request $request, string $entity): JsonResponse
    {
        $metas = $entityManager->getMetadataFactory()->getAllMetadata();
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

        $query = $request->query;

        $result = $entityManager
            ->getRepository($class)
            ->findBy([],
                orderBy: $query->get('orderBy'),
                limit: $query->get('limit', 10),
                offset: $query->get('offset')
            );

        $rows = [];
        foreach ($result as $row) {
            if ($this->isGranted('id', $row)) {
                $rows[] = $row;
            }
        }

        return new JsonResponse([$entity => $rows]);
    }
}
