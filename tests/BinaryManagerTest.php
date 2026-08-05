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
        $downloader->expects($this->once())
            ->method('download')
            ->with(PlatformResolver::getDownloadUrl($version), $expectedPath);

        $manager = new BinaryManager($this->tempDir, $downloader);
        $manager->resolveBinaryPath($version);
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
