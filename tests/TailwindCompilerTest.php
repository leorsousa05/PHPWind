<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\BinaryManager;
use PHPWind\Binary\Downloader;
use PHPWind\Binary\ProcessResult;
use PHPWind\Binary\Runner;
use PHPWind\Compiler\CompilationResult;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\BinaryDownloadException;
use PHPWind\Exception\BinaryExecutionException;
use PHPWind\Exception\InvalidConfigurationException;

class TailwindCompilerTest extends TestCase
{
    private string $input;

    protected function setUp(): void
    {
        $this->input = tempnam(sys_get_temp_dir(), 'phpwind-input-');
        file_put_contents($this->input, '/* input */');
    }

    protected function tearDown(): void
    {
        @unlink($this->input);
    }

    public function testCompileReturnsExitCodeForBackwardCompatibility(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->method('runResult')->willReturn(new ProcessResult(exitCode: 42));

        $compiler = new TailwindCompiler($binaryManager, $runner);
        $exitCode = $compiler->compile(new PHPWindConfig(inputCss: $this->input));

        $this->assertSame(42, $exitCode);
    }

    public function testCompileResultReturnsStructuredResult(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->expects($this->once())
            ->method('resolveBinaryPath')
            ->with('v4.0.0')
            ->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->expects($this->once())
            ->method('runResult')
            ->with('/path/to/tailwind', $this->isInstanceOf(PHPWindConfig::class))
            ->willReturn(new ProcessResult(exitCode: 0));

        $compiler = new TailwindCompiler($binaryManager, $runner);
        $result = $compiler->compileResult(new PHPWindConfig(inputCss: $this->input, outputCss: 'public/css/app.css'));

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertSame(0, $result->exitCode);
        $this->assertSame('public/css/app.css', $result->outputPath);
        $this->assertGreaterThanOrEqual(0, $result->durationMs);
    }

    public function testCompileResultPropagatesProcessOutput(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->method('runResult')->willReturn(new ProcessResult(
            exitCode: 1,
            stdout: 'some stdout',
            stderr: 'some stderr'
        ));

        $compiler = new TailwindCompiler($binaryManager, $runner);
        $result = $compiler->compileResult(new PHPWindConfig(inputCss: $this->input));

        $this->assertSame('some stdout', $result->stdout);
        $this->assertSame('some stderr', $result->stderr);
    }

    public function testCompileResultValidatesConfig(): void
    {
        $compiler = new TailwindCompiler();

        $this->expectException(InvalidConfigurationException::class);
        $compiler->compileResult(new PHPWindConfig(inputCss: ''));
    }

    public function testCompileResultRejectsMissingInputBeforeResolvingBinary(): void
    {
        $manager = $this->createMock(BinaryManager::class);
        $manager->expects($this->never())->method('resolveBinaryPath');
        $compiler = new TailwindCompiler($manager);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Input CSS file does not exist');
        $compiler->compileResult(new PHPWindConfig(inputCss: $this->input . '.missing'));
    }

    public function testCompileResultPropagatesBinaryDownloadException(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')
            ->willThrowException(new BinaryDownloadException('network error'));

        $runner = $this->createMock(Runner::class);
        $compiler = new TailwindCompiler($binaryManager, $runner);

        $this->expectException(BinaryDownloadException::class);
        $compiler->compileResult(new PHPWindConfig(inputCss: $this->input));
    }

    public function testCompileResultPropagatesBinaryExecutionException(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->method('runResult')
            ->willThrowException(new BinaryExecutionException('exec failed'));

        $compiler = new TailwindCompiler($binaryManager, $runner);

        $this->expectException(BinaryExecutionException::class);
        $compiler->compileResult(new PHPWindConfig(inputCss: $this->input));
    }

    public function testDefaultBinaryManagerReceivesConfiguredDownloaderSettings(): void
    {
        $config = new PHPWindConfig(inputCss: $this->input, downloadTimeout: 37, verifySsl: false);
        // Exercise the exact production factory without resolving/downloading a binary.
        $factoryCompiler = new class extends TailwindCompiler {
            public function create(PHPWindConfig $config): BinaryManager { return $this->createDefaultBinaryManager($config); }
        };
        $manager = $factoryCompiler->create($config);
        $property = new \ReflectionProperty(BinaryManager::class, 'downloader');
        $downloader = $property->getValue($manager);
        $timeout = new \ReflectionProperty(Downloader::class, 'timeoutSeconds');
        $ssl = new \ReflectionProperty(Downloader::class, 'verifySsl');
        $this->assertSame(37, $timeout->getValue($downloader));
        $this->assertFalse($ssl->getValue($downloader));
    }
}
