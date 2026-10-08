<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\PlatformResolver;
use PHPWind\Command\CleanHandler;
use PHPWind\Command\InitHandler;
use PHPWind\Command\DoctorHandler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class CommandHandlerTest extends TestCase
{
    use RemovesTempDirectories;
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
        $binaryFile = $binaryDir . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();
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

    public function testDoctorReportsMissingCacheWithoutSideEffectsAndInputFailure(): void
    {
        $cache = $this->tempDir . '/not-created/cache';
        $result = (new DoctorHandler())->diagnose(new PHPWindConfig(inputCss: $this->tempDir . '/missing.css', outputCss: $this->tempDir . '/out.css', binaryDir: $cache));
        $this->assertSame(1, $result['exitCode']);
        $this->assertStringContainsString('Input CSS readable', $result['output']);
        $this->assertStringContainsString('Summary:', $result['output']);
        $this->assertStringContainsString('offline', $result['output']);
        $this->assertStringContainsString('Binary cache entry: not installed', $result['output']);
        $this->assertDirectoryDoesNotExist(dirname($cache));
        $this->assertFileDoesNotExist($this->tempDir . '/out.css');
    }

    public function testDoctorSuccessReportsValidCacheAndDoesNotModifyFilesystem(): void
    {
        $input = $this->tempDir . '/input.css';
        $binaryDir = $this->tempDir . '/bin';
        $outputDir = $this->tempDir . '/new';
        mkdir($binaryDir);
        mkdir($outputDir);
        file_put_contents($input, '/* input */');
        $binaryPath = $binaryDir . '/' . PlatformResolver::getVersionedBinaryName(PlatformResolver::DEFAULT_VERSION);
        file_put_contents($binaryPath, 'binary');
        if (PHP_OS_FAMILY !== 'Windows') chmod($binaryPath, 0755);
        $before = scandir($this->tempDir);

        $result = (new DoctorHandler())->diagnose(new PHPWindConfig(inputCss: $input, outputCss: $outputDir . '/out.css', binaryDir: $binaryDir));

        $this->assertSame(0, $result['exitCode']);
        $this->assertStringContainsString('Binary cache entry: present/valid', $result['output']);
        $this->assertStringContainsString('Tailwind version:', $result['output']);
        $this->assertSame($before, scandir($this->tempDir));
        $this->assertFileDoesNotExist($this->tempDir . '/new/out.css');
    }

    public function testDoctorLabelsInvalidCacheWithoutTreatingItAsRequiredFailure(): void
    {
        $input = $this->tempDir . '/input.css';
        $binaryDir = $this->tempDir . '/bin';
        mkdir($binaryDir);
        file_put_contents($input, '/* input */');
        file_put_contents($binaryDir . '/' . PlatformResolver::getVersionedBinaryName(PlatformResolver::DEFAULT_VERSION), '');

        $result = (new DoctorHandler())->diagnose(new PHPWindConfig(inputCss: $input, outputCss: $this->tempDir . '/out.css', binaryDir: $binaryDir));

        $this->assertSame(0, $result['exitCode']);
        $this->assertStringContainsString('Binary cache entry: present/invalid', $result['output']);
    }
}
