<?php declare(strict_types=1);
namespace Imbo\Storage;

/**
 * @coversDefaultClass Imbo\Storage\Filesystem
 * @group integration
 */
class FilesystemIntegrationTest extends StorageTests {
    private string $path;

    protected function getAdapter() : Filesystem {
        $this->path = sys_get_temp_dir() . '/imbo-filesystem-integration-test-' . uniqid();
        mkdir($this->path);

        return new Filesystem($this->path);
    }

    protected function tearDown() : void {
        if (is_dir($this->path)) {
            $this->rmdir($this->path);
        }

        parent::tearDown();
    }

    /**
     * Recursively delete the test directory
     *
     * @param string $path Path to a file or a directory
     */
    private function rmdir($path) : void {
        $paths = glob($path . '/*');

        if (false === $paths) {
            return;
        }

        foreach ($paths as $file) {
            if (is_dir($file)) {
                $this->rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}