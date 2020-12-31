<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityClassFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findClass(string $entity): string
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
}
