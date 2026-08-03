<?php

namespace PHPWind\Config;

class PHPWindConfig
{
    public function __construct(
        public string $inputCss = 'resources/css/app.css',
        public string $outputCss = 'public/css/app.css',
        public string $binaryDir = 'vendor/bin/tailwind-cli',
        public string $version = 'v4.0.0',
        public bool $minify = false,
        public bool $watch = false
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            inputCss: $config['input_css'] ?? 'resources/css/app.css',
            outputCss: $config['output_css'] ?? 'public/css/app.css',
            binaryDir: $config['binary_dir'] ?? 'vendor/bin/tailwind-cli',
            version: $config['version'] ?? 'v4.0.0',
            minify: (bool) ($config['minify'] ?? false),
            watch: (bool) ($config['watch'] ?? false)
        );
    }
}
