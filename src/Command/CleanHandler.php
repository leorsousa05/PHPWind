<?php

namespace PHPWind\Command;

use PHPWind\Binary\PlatformResolver;
use PHPWind\Config\PHPWindConfig;

class CleanHandler
{
    public function handle(PHPWindConfig $config, bool $cleanOutput = false): bool
    {
        $binaryPath = rtrim($config->binaryDir, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();

        if (file_exists($binaryPath)) {
            unlink($binaryPath);
        }

        if (is_dir($config->binaryDir)) {
            @rmdir($config->binaryDir);
        }

        if ($cleanOutput && file_exists($config->outputCss)) {
            unlink($config->outputCss);
        }

        return true;
    }
}
