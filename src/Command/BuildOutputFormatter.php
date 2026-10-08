<?php

declare(strict_types=1);

namespace PHPWind\Command;

use PHPWind\Compiler\CompilationResult;

final class BuildOutputFormatter
{
    public static function success(): string
    {
        return 'Tailwind CSS build completed successfully.';
    }

    public static function failure(CompilationResult $result): string
    {
        $message = 'Tailwind CSS build failed (exit code ' . $result->exitCode . ').';
        $details = trim($result->stderr . "\n" . $result->stdout);

        return $details === '' ? $message : $message . "\n" . $details;
    }

    public static function exception(\Throwable $exception): string
    {
        return 'Tailwind CSS build failed: ' . $exception->getMessage();
    }
}
