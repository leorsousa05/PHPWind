<?php

namespace PHPWind\Compiler;

use PHPWind\Binary\Downloader;
use PHPWind\Binary\Runner;
use PHPWind\Config\PHPWindConfig;

class TailwindCompiler
{
    private Downloader $downloader;
    private Runner $runner;

    public function __construct(?Downloader $downloader = null, ?Runner $runner = null)
    {
        $this->downloader = $downloader ?? new Downloader();
        $this->runner = $runner ?? new Runner();
    }

    public function compile(PHPWindConfig $config): int
    {
        $binaryPath = $this->downloader->ensureBinaryInstalled(
            $config->binaryDir,
            $config->version
        );

        return $this->runner->run($binaryPath, $config);
    }
}
