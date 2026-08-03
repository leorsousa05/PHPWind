<?php

namespace PHPWind\Middleware;

use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;

class OnDemandCompilerMiddleware
{
    private PHPWindConfig $config;
    private TailwindCompiler $compiler;
    private bool $isDev;

    public function __construct(PHPWindConfig $config, bool $isDev = true, ?TailwindCompiler $compiler = null)
    {
        $this->config = $config;
        $this->isDev = $isDev;
        $this->compiler = $compiler ?? new TailwindCompiler();
    }

    public function handle(callable $next, mixed $request): mixed
    {
        if ($this->isDev) {
            $this->compiler->compile($this->config);
        }

        return $next($request);
    }
}
