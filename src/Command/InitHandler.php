<?php

namespace PHPWind\Command;

use PHPWind\Config\PHPWindConfig;

class InitHandler
{
    public function handle(PHPWindConfig $config): bool
    {
        $inputDir = dirname($config->inputCss);
        if (!is_dir($inputDir)) {
            mkdir($inputDir, 0755, true);
        }

        if (!file_exists($config->inputCss)) {
            file_put_contents($config->inputCss, "@import \"tailwindcss\";\n");
        }

        return true;
    }
}
