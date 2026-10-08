<?php

declare(strict_types=1);

namespace PHPWind\Compiler;

use PHPWind\Binary\BinaryManager;
use PHPWind\Binary\Runner;
use PHPWind\Binary\Downloader;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\InvalidConfigurationException;

class TailwindCompiler
{
    private ?BinaryManager $binaryManager;
    private Runner $runner;

    public function __construct(?BinaryManager $binaryManager = null, ?Runner $runner = null)
    {
        $this->binaryManager = $binaryManager;
        $this->runner = $runner ?? new Runner();
    }

    public function compile(PHPWindConfig $config): int
    {
        return $this->compileResult($config)->exitCode;
    }

    public function compileResult(PHPWindConfig $config): CompilationResult
    {
        $config->validate();
        if (!is_file($config->inputCss) || !is_readable($config->inputCss)) {
            throw new InvalidConfigurationException(sprintf('Input CSS file does not exist or is not readable: "%s"', $config->inputCss));
        }

        $start = hrtime(true);
        $binaryManager = $this->binaryManager ?? $this->createDefaultBinaryManager($config);
        $binaryPath = $binaryManager->resolveBinaryPath($config->version);
        $result = $this->runner->runResult($binaryPath, $config);
        $durationMs = (int) round((hrtime(true) - $start) / 1_000_000);

        return new CompilationResult(
            exitCode: $result->exitCode,
            outputPath: $config->outputCss,
            durationMs: $durationMs,
            stdout: $result->stdout,
            stderr: $result->stderr
        );
    }

    protected function createDefaultBinaryManager(PHPWindConfig $config): BinaryManager
    {
        return new BinaryManager($config->binaryDir, new Downloader($config->downloadTimeout, $config->verifySsl));
    }
}
