<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\BinaryManager;
use PHPWind\Binary\Downloader;
use PHPWind\Binary\PlatformResolver;
use PHPWind\Exception\BinaryDownloadException;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class BinaryManagerTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_binary_manager_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
        $this->tempDir = realpath($this->tempDir) ?: $this->tempDir;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testResolveBinaryPathReturnsExistingVersionedBinaryWithoutDownloading(): void
    {
        $version = 'v4.0.0';
        $binaryName = PlatformResolver::getVersionedBinaryName($version);
        $binaryPath = $this->tempDir . DIRECTORY_SEPARATOR . $binaryName;
        file_put_contents($binaryPath, 'dummy-binary');
        if (PHP_OS_FAMILY !== 'Windows') chmod($binaryPath, 0755);

        $downloader = $this->createMock(Downloader::class);
        $downloader->expects($this->never())->method('download');

        $manager = new BinaryManager($this->tempDir, $downloader);
        $resolved = $manager->resolveBinaryPath($version);

        $this->assertSame($binaryPath, $resolved);
    }

    public function testResolveBinaryPathTriggersDownloadWhenVersionedBinaryMissing(): void
    {
        $version = 'v4.0.0';
        $binaryName = PlatformResolver::getVersionedBinaryName($version);
        $expectedPath = $this->tempDir . DIRECTORY_SEPARATOR . $binaryName;

        $downloader = $this->createMock(Downloader::class);
        $downloader->expects($this->once())->method('download')->with(PlatformResolver::getDownloadUrl($version), $expectedPath)
            ->willReturnCallback(static function ($url, $path): void { file_put_contents($path, 'binary'); if (PHP_OS_FAMILY !== 'Windows') chmod($path, 0755); });

        $manager = new BinaryManager($this->tempDir, $downloader);
        $manager->resolveBinaryPath($version);
    }

    public function testEmptyCachedBinaryIsRemovedAndRedownloaded(): void
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName('v4.0.0');
        file_put_contents($path, '');
        $downloader = $this->createMock(Downloader::class);
        $downloader->expects($this->once())->method('download')->willReturnCallback(static function ($url, $target): void { file_put_contents($target, 'binary'); if (PHP_OS_FAMILY !== 'Windows') chmod($target, 0755); });
        self::assertSame($path, (new BinaryManager($this->tempDir, $downloader))->resolveBinaryPath('v4.0.0'));
    }

    public function testNonEmptyInvalidCachedBinaryIsRemovedAndRedownloaded(): void
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName('v4.0.0');
        file_put_contents($path, 'invalid');
        if (PHP_OS_FAMILY !== 'Windows') chmod($path, 0644);
        $downloader = $this->createMock(Downloader::class);
        $downloader->expects(self::once())->method('download')->willReturnCallback(static function ($url, $target): void { file_put_contents($target, 'binary'); if (PHP_OS_FAMILY !== 'Windows') chmod($target, 0755); });
        self::assertSame($path, (new BinaryManager($this->tempDir, $downloader))->resolveBinaryPath('v4.0.0'));
    }

    public function testConcurrentProcessesResolveSameBinaryWithOneDownload(): void
    {
        if (PHP_OS_FAMILY === 'Windows' || !function_exists('proc_open')) {
            self::markTestSkipped('Subprocess concurrency test requires proc_open on Unix.');
        }
        $autoload = dirname(__DIR__) . '/vendor/autoload.php';
        $downloader = $this->tempDir . '/downloader.php';
        $code = '<?php require ' . var_export($autoload, true) . '; class SlowDownloader extends \\PHPWind\\Binary\\Downloader { public function download(string $url, string $path): void { file_put_contents(' . var_export($this->tempDir . '/downloads', true) . ', "download\\n", FILE_APPEND | LOCK_EX); usleep(400000); file_put_contents($path, "binary"); chmod($path, 0755); } } (new \\PHPWind\\Binary\\BinaryManager(' . var_export($this->tempDir, true) . ', new SlowDownloader()))->resolveBinaryPath("v4.0.0");';
        file_put_contents($downloader, $code);
        $processes = [];
        for ($i = 0; $i < 2; $i++) {
            $processes[] = proc_open([PHP_BINARY, $downloader], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            fclose($pipes[0]);
            $stdout[] = $pipes[1];
            $stderr[] = $pipes[2];
        }
        foreach ($processes as $i => $process) {
            $out = stream_get_contents($stdout[$i]);
            $err = stream_get_contents($stderr[$i]);
            fclose($stdout[$i]);
            fclose($stderr[$i]);
            self::assertSame(0, proc_close($process), $out . $err);
        }
        self::assertSame(1, substr_count((string) file_get_contents($this->tempDir . '/downloads'), 'download'));
        self::assertFileExists($this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName('v4.0.0'));
    }

    public function testResolveBinaryPathPropagatesDownloadException(): void
    {
        $version = 'v4.0.0';

        $downloader = $this->createMock(Downloader::class);
        $downloader->method('download')
            ->willThrowException(new BinaryDownloadException('network error'));

        $manager = new BinaryManager($this->tempDir, $downloader);

        $this->expectException(BinaryDownloadException::class);
        $manager->resolveBinaryPath($version);
    }

    public function testClearCachedBinaryRemovesGenericBinary(): void
    {
        $genericBinary = $this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();
        file_put_contents($genericBinary, 'dummy');

        $manager = new BinaryManager($this->tempDir);
        $removed = $manager->clearCachedBinary();

        $this->assertTrue($removed);
        $this->assertFileDoesNotExist($genericBinary);
    }

    public function testClearCachedBinaryRemovesSpecificVersion(): void
    {
        $version = 'v3.4.17';
        $binaryName = PlatformResolver::getVersionedBinaryName($version);
        $binaryPath = $this->tempDir . DIRECTORY_SEPARATOR . $binaryName;
        file_put_contents($binaryPath, 'dummy');

        $manager = new BinaryManager($this->tempDir);
        $removed = $manager->clearCachedBinary($version);

        $this->assertTrue($removed);
        $this->assertFileDoesNotExist($binaryPath);
    }

    public function testClearCachedBinaryRemovesAllVersionedBinaries(): void
    {
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName('v3.4.17'), 'dummy');
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName('v4.0.0'), 'dummy');

        $manager = new BinaryManager($this->tempDir);
        $removed = $manager->clearCachedBinary();

        $this->assertTrue($removed);
        $this->assertSame(0, count(glob($this->tempDir . DIRECTORY_SEPARATOR . 'tailwind-v*')));
    }

    public function testClearCachedBinaryReturnsFalseWhenNothingRemoved(): void
    {
        $manager = new BinaryManager($this->tempDir);
        $removed = $manager->clearCachedBinary();

        $this->assertFalse($removed);
    }
}
