<?php
declare(strict_types=1);

namespace App\Tests\Common;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait FloorFileBehaviour
{
    abstract protected function getContainer(): ContainerInterface;

    private ?array $files;

    private string $path;

    /**
     * @before
     */
    protected function saveExistingFiles(): void
    {
        $this->path = sprintf('%s%spublic%s%s',
            $this->getContainer()->getParameter('kernel.project_dir'),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $this->getContainer()->getParameter('floor_path')
        );

        $this->files = scandir($this->path);
    }

    /**
     * @after
     */
    protected function removeNewFiles(): void
    {
        $files = scandir($this->path);
        $diff = array_diff($files, $this->files);

        foreach ($diff as $fileName) {
            unlink(sprintf('%s%s%s', $this->path, DIRECTORY_SEPARATOR, $fileName));
        }
    }
}
