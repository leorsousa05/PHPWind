<?php

declare(strict_types=1);

namespace PHPWind\Compiler;

class CompilationResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $outputPath,
        public readonly int $durationMs
    ) {}
}
