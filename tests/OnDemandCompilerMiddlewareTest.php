<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\ChangeDetection\FileChangeDetector;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Middleware\OnDemandCompilerMiddleware;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class OnDemandCompilerMiddlewareTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_middleware_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testCompilesOnlyWhenInputChanges(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);

        $calls = 0;
        $compiler = $this->createMock(TailwindCompiler::class);
        $compiler->method('compile')->willReturnCallback(function () use (&$calls): int {
            $calls++;
            return 0;
        });

        $detector = new FileChangeDetector($this->tempDir . '/state.json');
        $middleware = new OnDemandCompilerMiddleware($config, true, $compiler, true, $detector);

        $next = fn (mixed $request): mixed => $request;

        $middleware->handle($next, 'req');   // first run -> compile
        $middleware->handle($next, 'req');   // unchanged  -> skip
        $this->assertSame(1, $calls);

        touch($input, time() + 10);
        clearstatcache(true, $input);

        $middleware->handle($next, 'req');   // changed -> compile
        $this->assertSame(2, $calls);
    }

    public function testSkipsCompileWhenNotDev(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);

        $compiler = $this->createMock(TailwindCompiler::class);
        $compiler->expects($this->never())->method('compile');

        $middleware = new OnDemandCompilerMiddleware($config, false, $compiler);

        $middleware->handle(fn (mixed $r): mixed => $r, 'req');
    }

    public function testAlwaysCompilesWhenCheckForChangesIsFalse(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);

        $calls = 0;
        $compiler = $this->createMock(TailwindCompiler::class);
        $compiler->method('compile')->willReturnCallback(function () use (&$calls): int {
            $calls++;
            return 0;
        });

        $detector = new FileChangeDetector($this->tempDir . '/state.json');
        $middleware = new OnDemandCompilerMiddleware($config, true, $compiler, false, $detector);

        $next = fn (mixed $r): mixed => $r;
        $middleware->handle($next, 'req');
        $middleware->handle($next, 'req');

        $this->assertSame(2, $calls);
    }

    public function testNextIsCalled(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);

        $compiler = $this->createMock(TailwindCompiler::class);
        $compiler->method('compile')->willReturn(0);

        $detector = new FileChangeDetector($this->tempDir . '/state.json');
        $middleware = new OnDemandCompilerMiddleware($config, true, $compiler, true, $detector);
        $called = false;
        $result = $middleware->handle(function (mixed $request) use (&$called): mixed {
            $called = true;
            return 'done';
        }, 'req');

        $this->assertTrue($called);
        $this->assertSame('done', $result);
    }
}
