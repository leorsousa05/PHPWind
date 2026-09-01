<?php

declare(strict_types=1);

namespace PHPWind\Middleware;

use PHPWind\ChangeDetection\FileChangeDetector;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;

class OnDemandCompilerMiddleware
{
    private PHPWindConfig $config;
    private TailwindCompiler $compiler;
    private bool $isDev;
    private bool $checkForChanges;
    private FileChangeDetector $detector;

    public function __construct(
        PHPWindConfig $config,
        bool $isDev = true,
        ?TailwindCompiler $compiler = null,
        bool $checkForChanges = true,
        ?FileChangeDetector $detector = null
    ) {
        $this->config = $config;
        $this->isDev = $isDev;
        $this->compiler = $compiler ?? new TailwindCompiler();
        $this->checkForChanges = $checkForChanges;
        $this->detector = $detector ?? new FileChangeDetector();
    }

    public function handle(callable $next, mixed $request): mixed
    {
        if ($this->isDev && $this->shouldCompile()) {
            $this->compiler->compile($this->config);
            $this->detector->record($this->config);
        }

        return $next($request);
    }

    private function shouldCompile(): bool
    {
        if (!$this->checkForChanges) {
            return true;
        }

        return $this->detector->hasChanged($this->config);
    }
}
