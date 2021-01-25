<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Floor;
use App\Tests\Common\DemodataTrait;
use App\Tests\Common\FloorFileBehaviour;
use App\Tests\Common\IntegrationTestBehaviour;
use App\Tests\Common\WebTestBehaviour;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FloorMapControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use WebTestBehaviour;
    use DemodataTrait;
    use FloorFileBehaviour;

    private Floor $floor;

    protected function setUp(): void
    {
        $this->floor = $this->addFloor();
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

    public function testUploadAsAnonymousIsRestricted(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST,
            sprintf('/api/floor/%s/upload', $this->floor->getId()),
        );

        static::assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testUploadAsUserIsRestricted(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST,
            sprintf('/api/floor/%s/upload', $this->floor->getId()),
            server: ['HTTP_auth-token' => $this->user->getAuthTokens()->first()->getToken()],
        );

        static::assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testUploadRequiresFiles(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST,
            sprintf('/api/floor/%s/upload', $this->floor->getId()),
            server: ['HTTP_auth-token' => $this->adminUser->getAuthTokens()->first()->getToken()],
        );

        static::assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testUploadInvalidFloor(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST,
            sprintf('/api/floor/%s/upload', 1000000),
            files: ['foo' => $this->getFile()],
            server: ['HTTP_auth-token' => $this->adminUser->getAuthTokens()->first()->getToken()],
        );

        static::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testUploadFile(): void
    {
        $client = $this->getClient();
        $client->request(Request::METHOD_POST,
            sprintf('/api/floor/%s/upload', $this->floor->getId()),
            files: ['foo' => $this->getFile()],
            server: ['HTTP_auth-token' => $this->adminUser->getAuthTokens()->first()->getToken()],
        );

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    private function getFile(): UploadedFile
    {
        return new UploadedFile(__DIR__ . '/../_file_fixtures/test_image.png', 'test_image.png');
    }
}
