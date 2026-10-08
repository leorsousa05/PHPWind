<?php

declare(strict_types=1);

namespace PHPWind\Config;

use PHPWind\Binary\PlatformResolver;
use PHPWind\Exception\InvalidConfigurationException;

class PHPWindConfig
{
    public function __construct(
        public string $inputCss = 'resources/css/app.css',
        public string $outputCss = 'public/css/app.css',
        public string $binaryDir = 'vendor/bin/tailwind-cli',
        public string $version = PlatformResolver::DEFAULT_VERSION,
        public bool $minify = false,
        public bool $watch = false,
        public int $downloadTimeout = 120,
        public bool $verifySsl = true
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            inputCss: $config['input_css'] ?? 'resources/css/app.css',
            outputCss: $config['output_css'] ?? 'public/css/app.css',
            binaryDir: $config['binary_dir'] ?? 'vendor/bin/tailwind-cli',
            version: $config['version'] ?? PlatformResolver::DEFAULT_VERSION,
            minify: (bool) ($config['minify'] ?? false),
            watch: (bool) ($config['watch'] ?? false),
            downloadTimeout: self::parseTimeout($config['download_timeout'] ?? 120),
            verifySsl: filter_var($config['verify_ssl'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'input_css' => $this->inputCss,
            'output_css' => $this->outputCss,
            'binary_dir' => $this->binaryDir,
            'version' => $this->version,
            'minify' => $this->minify,
            'watch' => $this->watch,
            'download_timeout' => $this->downloadTimeout,
            'verify_ssl' => $this->verifySsl,
        ];
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function validate(): void
    {
        if ($this->downloadTimeout <= 0) {
            throw new InvalidConfigurationException('downloadTimeout must be a positive integer.');
        }
        if (trim($this->inputCss) === '') {
            throw new InvalidConfigurationException('inputCss cannot be empty.');
        }

        if (trim($this->outputCss) === '') {
            throw new InvalidConfigurationException('outputCss cannot be empty.');
        }

        if (trim($this->binaryDir) === '') {
            throw new InvalidConfigurationException('binaryDir cannot be empty.');
        }

        $version = preg_replace('/^v/', '', $this->version);
        if (!is_string($version) || !preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/', $version)) {
            throw new InvalidConfigurationException(
                sprintf('version must be a valid semantic version (e.g., v4.0.0). Got: "%s"', $this->version)
            );
        }
    }

    private static function parseTimeout(mixed $timeout): int
    {
        if (is_int($timeout)) return $timeout;
        if (is_string($timeout) && preg_match('/^[1-9][0-9]*$/', $timeout)) return (int) $timeout;
        throw new InvalidConfigurationException('download_timeout must be a positive integer.');
    }
}
