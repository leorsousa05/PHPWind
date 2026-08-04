<?php

declare(strict_types=1);

namespace PHPWind\Compiler;

use PHPWind\Binary\BinaryManager;
use PHPWind\Binary\Runner;
use PHPWind\Config\PHPWindConfig;

class TailwindCompiler
{
    private BinaryManager $binaryManager;
    private Runner $runner;

    public function __construct(?BinaryManager $binaryManager = null, ?Runner $runner = null)
    {
        $this->binaryManager = $binaryManager ?? new BinaryManager('vendor/bin/tailwind-cli');
        $this->runner = $runner ?? new Runner();
    }

    public function compile(PHPWindConfig $config): int
    {
        return $this->compileResult($config)->exitCode;
    }

    public function compileResult(PHPWindConfig $config): CompilationResult
    {
        $config->validate();

        $start = hrtime(true);
        $binaryPath = $this->binaryManager->resolveBinaryPath($config->version);
        $exitCode = $this->runner->run($binaryPath, $config);
        $durationMs = (int) round((hrtime(true) - $start) / 1_000_000);

        return new CompilationResult(
            exitCode: $exitCode,
            outputPath: $config->outputCss,
            durationMs: $durationMs
        );
    }
}
