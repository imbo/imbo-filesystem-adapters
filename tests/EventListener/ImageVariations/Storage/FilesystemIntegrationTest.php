<?php declare(strict_types=1);
namespace Imbo\EventListener\ImageVariations\Storage;

/**
 * @coversDefaultClass Imbo\EventListener\ImageVariations\Storage\Filesystem
 */
class FilesystemIntegrationTest extends StorageTests
{
    private string $path;

    protected function getAdapter(): StorageInterface
    {
        return new Filesystem($this->path);
    }

    public function setUp(): void
    {
        $this->path = sys_get_temp_dir() . '/imbo-eventlistener-imagevariations-storage-filesystem-integration-test-' . uniqid();

        if (is_dir($this->path)) {
            $this->rmdir($this->path);
        }

        mkdir($this->path);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->path)) {
            $this->rmdir($this->path);
        }

        parent::tearDown();
    }

    private function rmdir(string $path): void
    {
        foreach (glob($path . '/*') as $file) {
            if (is_dir($file)) {
                $this->rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
