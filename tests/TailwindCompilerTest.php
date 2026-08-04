<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\BinaryManager;
use PHPWind\Binary\Runner;
use PHPWind\Compiler\CompilationResult;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\BinaryDownloadException;
use PHPWind\Exception\BinaryExecutionException;
use PHPWind\Exception\InvalidConfigurationException;

class TailwindCompilerTest extends TestCase
{
    public function testCompileReturnsExitCodeForBackwardCompatibility(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->method('run')->willReturn(42);

        $compiler = new TailwindCompiler($binaryManager, $runner);
        $exitCode = $compiler->compile(new PHPWindConfig());

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
            ->method('run')
            ->with('/path/to/tailwind', $this->isInstanceOf(PHPWindConfig::class))
            ->willReturn(0);

        $compiler = new TailwindCompiler($binaryManager, $runner);
        $result = $compiler->compileResult(new PHPWindConfig(outputCss: 'public/css/app.css'));

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertSame(0, $result->exitCode);
        $this->assertSame('public/css/app.css', $result->outputPath);
        $this->assertGreaterThanOrEqual(0, $result->durationMs);
    }

    public function testCompileResultValidatesConfig(): void
    {
        $compiler = new TailwindCompiler();

        $this->expectException(InvalidConfigurationException::class);
        $compiler->compileResult(new PHPWindConfig(inputCss: ''));
    }

    public function testCompileResultPropagatesBinaryDownloadException(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')
            ->willThrowException(new BinaryDownloadException('network error'));

        $runner = $this->createMock(Runner::class);
        $compiler = new TailwindCompiler($binaryManager, $runner);

        $this->expectException(BinaryDownloadException::class);
        $compiler->compileResult(new PHPWindConfig());
    }

    public function testCompileResultPropagatesBinaryExecutionException(): void
    {
        $binaryManager = $this->createMock(BinaryManager::class);
        $binaryManager->method('resolveBinaryPath')->willReturn('/path/to/tailwind');

        $runner = $this->createMock(Runner::class);
        $runner->method('run')
            ->willThrowException(new BinaryExecutionException('exec failed'));

        $compiler = new TailwindCompiler($binaryManager, $runner);

        $this->expectException(BinaryExecutionException::class);
        $compiler->compileResult(new PHPWindConfig());
    }
}
