<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\Downloader;
use PHPWind\Exception\BinaryDownloadException;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class DownloaderTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_downloader_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
        $this->tempDir = realpath($this->tempDir) ?: $this->tempDir;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testSuccessfulDownloadWritesFile(): void
    {
        $destination = $this->tempDir . DIRECTORY_SEPARATOR . 'tailwind-v4.0.0';
        $expectedContent = 'fake-binary-content';

        $downloader = new class($expectedContent) extends Downloader {
            private string $content;

            public function __construct(string $content)
            {
                parent::__construct();
                $this->content = $content;
            }

            protected function executeRequest(string $url, $fp): array
            {
                fwrite($fp, $this->content);
                return ['success' => true, 'httpCode' => 200, 'error' => ''];
            }
        };

        $downloader->download('https://example.com/tailwind', $destination);

        $this->assertFileExists($destination);
        $this->assertSame($expectedContent, file_get_contents($destination));

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertSame(0755, fileperms($destination) & 0777);
        }
    }

    public function testFailedHttpStatusRemovesPartialFileAndThrows(): void
    {
        $destination = $this->tempDir . DIRECTORY_SEPARATOR . 'tailwind-v4.0.0';

        $downloader = new class extends Downloader {
            protected function executeRequest(string $url, $fp): array
            {
                fwrite($fp, 'partial');
                return ['success' => true, 'httpCode' => 404, 'error' => ''];
            }
        };

        $this->expectException(BinaryDownloadException::class);
        $this->expectExceptionMessage('HTTP 404');

        try {
            $downloader->download('https://example.com/tailwind', $destination);
        } catch (BinaryDownloadException $e) {
            $this->assertFileDoesNotExist($destination);
            throw $e;
        }
    }

    public function testNetworkErrorRemovesPartialFileAndThrows(): void
    {
        $destination = $this->tempDir . DIRECTORY_SEPARATOR . 'tailwind-v4.0.0';

        $downloader = new class extends Downloader {
            protected function executeRequest(string $url, $fp): array
            {
                return ['success' => false, 'httpCode' => 0, 'error' => 'connection timeout'];
            }
        };

        $this->expectException(BinaryDownloadException::class);
        $this->expectExceptionMessage('connection timeout');

        try {
            $downloader->download('https://example.com/tailwind', $destination);
        } catch (BinaryDownloadException $e) {
            $this->assertFileDoesNotExist($destination);
            throw $e;
        }
    }

    public function testDownloadCreatesParentDirectory(): void
    {
        $destination = $this->tempDir . DIRECTORY_SEPARATOR . 'nested' . DIRECTORY_SEPARATOR . 'tailwind';

        $downloader = new class extends Downloader {
            protected function executeRequest(string $url, $fp): array
            {
                return ['success' => true, 'httpCode' => 200, 'error' => ''];
            }
        };

        $downloader->download('https://example.com/tailwind', $destination);

        $this->assertDirectoryExists(dirname($destination));
    }

    public function testEnsureBinaryInstalledReusesExistingFile(): void
    {
        $binaryPath = $this->tempDir . DIRECTORY_SEPARATOR . 'tailwind';
        if (PHP_OS_FAMILY === 'Windows') {
            $binaryPath .= '.exe';
        }
        file_put_contents($binaryPath, 'existing');

        $downloader = $this->getMockBuilder(Downloader::class)
            ->onlyMethods(['download'])
            ->getMock();
        $downloader->expects($this->never())->method('download');

        $resolved = $downloader->ensureBinaryInstalled($this->tempDir);

        $this->assertSame($binaryPath, $resolved);
    }
}
