<?php

declare(strict_types=1);

namespace PHPWind\Config;

use PHPWind\Exception\InvalidConfigurationException;

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

    /**
     * @throws InvalidConfigurationException
     */
    public function validate(): void
    {
        if (trim($this->inputCss) === '') {
            throw new InvalidConfigurationException('inputCss cannot be empty.');
        }

        if (trim($this->outputCss) === '') {
            throw new InvalidConfigurationException('outputCss cannot be empty.');
        }

        if (trim($this->binaryDir) === '') {
            throw new InvalidConfigurationException('binaryDir cannot be empty.');
        }

        $version = ltrim($this->version, 'v');
        if ($version === '' || !preg_match('/^\d+\.\d+\.\d+/', $version)) {
            throw new InvalidConfigurationException(
                sprintf('version must be a valid semantic version (e.g., v4.0.0). Got: "%s"', $this->version)
            );
        }
    }
}
