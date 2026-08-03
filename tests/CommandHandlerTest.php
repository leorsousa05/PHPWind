<?php

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Command\CleanHandler;
use PHPWind\Command\InitHandler;
use PHPWind\Config\PHPWindConfig;

class CommandHandlerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testInitHandlerCreatesInputCss(): void
    {
        $inputCss = $this->tempDir . '/resources/css/app.css';
        $config = new PHPWindConfig(inputCss: $inputCss);

        $handler = new InitHandler();
        $result = $handler->handle($config);

        $this->assertTrue($result);
        $this->assertFileExists($inputCss);
        $this->assertStringContainsString('@import "tailwindcss";', file_get_contents($inputCss));
    }

    public function testCleanHandlerRemovesBinaryAndOutput(): void
    {
        $binaryDir = $this->tempDir . '/bin/tailwind-cli';
        mkdir($binaryDir, 0755, true);
        $binaryFile = $binaryDir . '/tailwind.exe';
        file_put_contents($binaryFile, 'dummy');

        $outputCss = $this->tempDir . '/public/css/app.css';
        mkdir(dirname($outputCss), 0755, true);
        file_put_contents($outputCss, 'body{}');

        $config = new PHPWindConfig(binaryDir: $binaryDir, outputCss: $outputCss);

        $handler = new CleanHandler();
        $handler->handle($config, cleanOutput: true);

        $this->assertFileDoesNotExist($binaryFile);
        $this->assertFileDoesNotExist($outputCss);
    }

    private function removeDirectory(string $dir): void
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
