<?php
declare(strict_types=1);

namespace App\Tests\File;

use App\File\FileHandler;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\FloorFileBehaviour;
use App\Tests\Common\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandlerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use FloorFileBehaviour;
    use DemodataTrait;

    private FileHandler $fileHandler;

    protected function setUp(): void
    {
        $this->fileHandler = $this->getContainer()->get(FileHandler::class);
        $this->addCommonData();
    }

    /**
     * @before
     */
    protected function copyTestFiles(): void
    {
        copy(__DIR__ . '/../_file_fixtures/1x1.png', __DIR__ . '/../_file_fixtures/test_image.png');
    }

    /**
     * @afterClass
     */
    protected function cleanUpFiles(): void
    {
        unlink(__DIR__ . '/../_file_fixtures/test_image.png');
    }

    public function testUpdatesWithExistingMap(): void
    {
        $floor = $this->addFloor(floorPath: '/maps/foo.png');
        $this->fileHandler->uploadMap($floor, $this->getFile());

        static::assertSame('/maps/foo.png', $floor->getFloorPath());
    }

    public function testUpdatesPath(): void
    {
        $floor = $this->addFloor();
        $this->fileHandler->uploadMap($floor, $this->getFile());

        static::assertNotNull($floor->getFloorPath());
    }

    private function getFile(): UploadedFile
    {
        return new UploadedFile(__DIR__ . '/../_file_fixtures/test_image.png', 'test_image.png', test: true);
    }
}
