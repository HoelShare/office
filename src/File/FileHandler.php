<?php
declare(strict_types=1);

namespace App\File;

use App\Entity\Floor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileHandler
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $mapDirectory,
        private string $publicFolder,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function uploadMap(Floor $floor, UploadedFile $uploadedFile): void
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $floor->getFloorPath() ?? ($safeFilename . '-' . uniqid('', true) . '.' . $uploadedFile->guessExtension());

        $uploadedFile->move(
            sprintf('%s%s%s', $this->publicFolder, DIRECTORY_SEPARATOR, $this->mapDirectory),
            $newFilename
        );

        if ($floor->getFloorPath() !== null) {
            return;
        }

        $floor->setFloorPath(sprintf('%s%s%s', $this->mapDirectory, DIRECTORY_SEPARATOR, $newFilename));
        $this->entityManager->persist($floor);
        $this->entityManager->flush();
    }
}
