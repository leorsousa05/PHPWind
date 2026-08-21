<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\Runner;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\BinaryExecutionException;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class RunnerTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_runner_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testRunExecutesBinaryAndReturnsExitCode(): void
    {
        $binary = $this->createFakeBinary(0);
        $config = new PHPWindConfig(inputCss: 'resources/css/app.css', outputCss: 'public/css/app.css');

        $runner = new Runner();
        $exitCode = $runner->run($binary, $config);

        $this->assertSame(0, $exitCode);
    }

    public function testRunPassesMinifyFlag(): void
    {
        $binary = $this->createFakeBinary(0);
        $config = new PHPWindConfig(
            inputCss: 'resources/css/app.css',
            outputCss: 'public/css/app.css',
            minify: true
        );

        $runner = new Runner();
        $runner->run($binary, $config);

        $captured = file_get_contents($this->tempDir . DIRECTORY_SEPARATOR . 'captured.args');
        $this->assertStringContainsString('--minify', $captured);
    }

    public function testRunPassesWatchFlag(): void
    {
        $binary = $this->createFakeBinary(0);
        $config = new PHPWindConfig(
            inputCss: 'resources/css/app.css',
            outputCss: 'public/css/app.css',
            watch: true
        );

        $runner = new Runner();
        $runner->run($binary, $config);

        $captured = file_get_contents($this->tempDir . DIRECTORY_SEPARATOR . 'captured.args');
        $this->assertStringContainsString('--watch', $captured);
    }

    public function testRunEscapesPaths(): void
    {
        $binary = $this->createFakeBinary(0, true);
        $config = new PHPWindConfig(
            inputCss: 'resources/css/my app.css',
            outputCss: 'public/css/my app.css'
        );

        $runner = new Runner();
        $runner->run($binary, $config);

        $captured = file_get_contents($this->tempDir . DIRECTORY_SEPARATOR . 'captured.json');
        $args = json_decode($captured, true);

        $this->assertSame('resources/css/my app.css', $args[2] ?? '');
        $this->assertSame('public/css/my app.css', $args[4] ?? '');
    }

    public function testRunThrowsOnInvalidBinary(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('proc_open rarely returns false on Windows; test is Unix-specific.');
        }

        $config = new PHPWindConfig(inputCss: 'resources/css/app.css', outputCss: 'public/css/app.css');
        $runner = new Runner();

        $this->expectException(BinaryExecutionException::class);

        // Null byte in the command is invalid and forces proc_open to fail on Unix.
        $invalidBinary = $this->tempDir . DIRECTORY_SEPARATOR . "invalid\0binary";
        $runner->run($invalidBinary, $config);
    }

    public function testRunResultCapturesStdoutAndStderr(): void
    {
        $binary = $this->createOutputBinary(3);
        $config = new PHPWindConfig(inputCss: 'resources/css/app.css', outputCss: 'public/css/app.css');

        $runner = new Runner();
        $result = $runner->runResult($binary, $config);

        $this->assertSame(3, $result->exitCode);
        $this->assertStringContainsString('hello out', $result->stdout);
        $this->assertStringContainsString('hello err', $result->stderr);
    }

    public function testRunDelegatesToRunResultExitCode(): void
    {
        $binary = $this->createOutputBinary(7);
        $config = new PHPWindConfig(inputCss: 'resources/css/app.css', outputCss: 'public/css/app.css');

        $runner = new Runner();
        $exitCode = $runner->run($binary, $config);

        $this->assertSame(7, $exitCode);
    }

    private function createOutputBinary(int $exitCode): string
    {
        $script = $this->tempDir . DIRECTORY_SEPARATOR . 'fake-output.php';
        $code = <<<PHP
<?php
fwrite(STDOUT, "hello out\\n");
fwrite(STDERR, "hello err\\n");
exit({$exitCode});
PHP;

        file_put_contents($script, $code);

        return PHP_BINARY . ' ' . $script;
    }

    private function createFakeBinary(int $exitCode, bool $jsonCapture = false): string
    {
        $captureFile = $this->tempDir . DIRECTORY_SEPARATOR . ($jsonCapture ? 'captured.json' : 'captured.args');
        $script = $this->tempDir . DIRECTORY_SEPARATOR . 'fake-tailwind.php';

        $captureCode = $jsonCapture
            ? "file_put_contents('{$captureFile}', json_encode(\$argv));"
            : "file_put_contents('{$captureFile}', implode(' ', \$argv));";

        $code = <<<PHP
<?php
{$captureCode}
exit({$exitCode});
PHP;

        file_put_contents($script, $code);

        return PHP_BINARY . ' ' . $script;
    }
}
