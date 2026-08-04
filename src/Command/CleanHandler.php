<?php

declare(strict_types=1);

namespace PHPWind\Command;

use PHPWind\Binary\BinaryManager;
use PHPWind\Config\PHPWindConfig;

class CleanHandler
{
    public function handle(PHPWindConfig $config, bool $cleanOutput = false): bool
    {
        $config->validate();

        $manager = new BinaryManager($config->binaryDir);
        $manager->clearCachedBinary();

        if (is_dir($config->binaryDir)) {
            @rmdir($config->binaryDir);
        }

        if ($cleanOutput && file_exists($config->outputCss)) {
            unlink($config->outputCss);
        }

        return true;
    }
}
