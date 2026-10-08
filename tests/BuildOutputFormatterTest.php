<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Command\BuildOutputFormatter;
use PHPWind\Compiler\CompilationResult;

class BuildOutputFormatterTest extends TestCase
{
    public function testSharedSuccessAndFailureMessages(): void
    {
        $this->assertSame('Tailwind CSS build completed successfully.', BuildOutputFormatter::success());
        $result = new CompilationResult(23, 'out.css', 1, 'compiler stdout', 'compiler stderr');
        $this->assertSame("Tailwind CSS build failed (exit code 23).\ncompiler stderr\ncompiler stdout", BuildOutputFormatter::failure($result));
    }

    public function testSharedExceptionFailureMessage(): void
    {
        $this->assertSame('Tailwind CSS build failed: missing input', BuildOutputFormatter::exception(new \RuntimeException('missing input')));
    }
}
