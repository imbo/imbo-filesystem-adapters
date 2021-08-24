<?php declare(strict_types=1);
namespace Imbo\Storage;

use DateTime;
use Imbo\Exception\StorageException;
use PHPUnit\Framework\TestCase;
use TestFs\Device;
use TestFs\StreamWrapper as TestFs;

/**
 * @coversDefaultClass Imbo\Storage\Filesystem
 */
class FilesystemTest extends TestCase
{
    private string $user            = 'user';
    private string $imageIdentifier = 'image.png';

    protected function setUp(): void
    {
        if (!TestFs::register()) {
            $this->fail('Unable to register stream wrapper');
        }
    }

    protected function tearDown(): void
    {
        TestFs::unregister();
    }

    /**
     * @covers ::delete
     */
    public function testDeleteFileThatDoesNotExist(): void
    {
        $adapter = new Filesystem(TestFs::url('foobar'));
        $this->expectExceptionObject(new StorageException('File not found', 404));
        $adapter->delete($this->user, $this->imageIdentifier);
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $adapter = new Filesystem(TestFs::url('basedir'));

        $dir = TestFs::url(join('/', [
            'basedir',
            $this->user[0],
            $this->user[1],
            $this->user[2],
            $this->user,
            $this->imageIdentifier[0],
            $this->imageIdentifier[1],
            $this->imageIdentifier[2],
        ]));
        $filePath = sprintf('%s/%s', $dir, $this->imageIdentifier);

        mkdir($dir, 0777, true);
        touch($filePath);

        $this->assertTrue(is_file($filePath), 'Expected file to exist');
        $adapter->delete($this->user, $this->imageIdentifier);
        clearstatcache();
        $this->assertFalse(is_file($filePath), 'Did not expect file to exist');
    }

    /**
     * @covers ::store
     * @covers ::__construct
     */
    public function testStoreToUnwritablePath(): void
    {
        $image = 'some image data';
        $dir = TestFs::url('unwritableDirectory');

        mkdir($dir, 0000);

        $adapter = new Filesystem($dir);
        $this->expectExceptionObject(new StorageException('Could not store image', 500));
        $adapter->store($this->user, $this->imageIdentifier, $image);
    }

    /**
     * @covers ::store
     * @covers ::getImagePath
     */
    public function testStore(): void
    {
        $path = __DIR__ . '/../Fixtures/image.png';
        $imageData = (string) file_get_contents($path);

        $baseDir = TestFs::url('someDir');
        mkdir($baseDir);

        $adapter = new Filesystem($baseDir);
        $this->assertTrue($adapter->store($this->user, $this->imageIdentifier, $imageData));

        $this->assertTrue(
            is_file(TestFs::url('someDir/u/s/e/user/i/m/a/image.png')),
            'Expected file to exist',
        );
    }

    /**
     * @covers ::getImage
     */
    public function testGetImageFileThatDoesNotExist(): void
    {
        $adapter = new Filesystem('/tmp');
        $this->expectExceptionObject(new StorageException('File not found', 404));
        $adapter->getImage($this->user, $this->imageIdentifier);
    }

    /**
     * @covers ::getImage
     */
    public function testGetImage(): void
    {
        $dir = TestFs::url('basedir');
        mkdir($dir);
        $adapter = new Filesystem($dir);

        $filePath = TestFs::url(join('/', [
            'basedir',
            $this->user[0],
            $this->user[1],
            $this->user[2],
            $this->user,
            $this->imageIdentifier[0],
            $this->imageIdentifier[1],
            $this->imageIdentifier[2],
            $this->imageIdentifier,
        ]));

        mkdir(dirname($filePath), 0777, true);
        file_put_contents($filePath, 'some content');

        $this->assertSame('some content', $adapter->getImage($this->user, $this->imageIdentifier));
    }

    /**
     * @covers ::getLastModified
     */
    public function testGetLastModifiedWithFileThatDoesNotExist(): void
    {
        $adapter = new Filesystem('/some/path');
        $this->expectExceptionObject(new StorageException('File not found', 404));
        $adapter->getLastModified($this->user, $this->imageIdentifier);
    }

    /**
     * @covers ::getLastModified
     */
    public function testGetLastModified(): void
    {
        $dir = TestFs::url('basedir');
        $adapter = new Filesystem($dir);

        $filePath = TestFs::url(join('/', [
            'basedir',
            $this->user[0],
            $this->user[1],
            $this->user[2],
            $this->user,
            $this->imageIdentifier[0],
            $this->imageIdentifier[1],
            $this->imageIdentifier[2],
            $this->imageIdentifier,
        ]));

        mkdir(dirname($filePath), 0777, true);
        file_put_contents($filePath, 'some content');

        $this->assertInstanceOf(DateTime::class, $adapter->getLastModified($this->user, $this->imageIdentifier));
    }

    /**
     * @covers ::getStatus
     */
    public function testGetStatusWhenBaseDirIsNotWritable(): void
    {
        $dir = TestFs::url('dir');
        mkdir($dir, 0000);
        $adapter = new Filesystem($dir);
        $this->assertFalse($adapter->getStatus());
    }

    /**
     * @covers ::getStatus
     */
    public function testGetStatusWhenBaseDirIsWritable(): void
    {
        $dir = TestFs::url('dir');
        mkdir($dir);
        $adapter = new Filesystem($dir);
        $this->assertTrue($adapter->getStatus());
    }

    /**
     * @covers ::store
     */
    public function testStoreFileThatAlreadyExists(): void
    {
        $path = __DIR__ . '/../Fixtures/image.png';
        $imageData = (string) file_get_contents($path);

        $baseDir = TestFs::url('someDir');
        mkdir($baseDir);

        $adapter = new Filesystem($baseDir);
        $this->assertTrue($adapter->store($this->user, $this->imageIdentifier, $imageData));
        $imagePath = TestFs::url('someDir/u/s/e/user/i/m/a/image.png');

        $this->assertTrue(
            is_file($imagePath),
            'Expected file to exist',
        );

        touch($imagePath, 1476937431);
        clearstatcache();
        $this->assertSame(1476937431, $adapter->getLastModified($this->user, $this->imageIdentifier)->getTimestamp());
        $this->assertTrue($adapter->store($this->user, $this->imageIdentifier, $imageData));
        clearstatcache();
        $this->assertEqualsWithDelta(time(), $adapter->getLastModified($this->user, $this->imageIdentifier)->getTimestamp(), 1);
    }

    /**
     * @covers ::store
     */
    public function testThrowsExceptionOnEmptyDisk(): void
    {
        $path = __DIR__ . '/../Fixtures/image.png';
        $imageData = (string) file_get_contents($path);

        $baseDir = TestFs::url('someDir');
        mkdir($baseDir);

        /** @var Device */
        $device = TestFs::getDevice();
        $device->setDeviceSize(2);

        $adapter = new Filesystem($baseDir);

        $this->expectExceptionObject(new StorageException('Failed writing file to disk: tfs://someDir/u/s/e/user/i/m/a/image.png', 507));
        @$adapter->store($this->user, $this->imageIdentifier, $imageData);
    }
}
