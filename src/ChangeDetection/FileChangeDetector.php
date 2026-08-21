<?php

declare(strict_types=1);

namespace PHPWind\ChangeDetection;

use PHPWind\Config\PHPWindConfig;

final class FileChangeDetector
{
    public function __construct(private string $stateFile = '.phpwind/.cache/middleware.json') {}

    public function hasChanged(PHPWindConfig $config): bool
    {
        $current = $this->fingerprint($config);

        if ($current === null) {
            // Cannot fingerprint the input file; treat as changed so a
            // subsequent compile is attempted.
            return true;
        }

        return $this->read() !== $current;
    }

    public function record(PHPWindConfig $config): void
    {
        $current = $this->fingerprint($config);

        if ($current === null) {
            return;
        }

        $this->write($current);
    }

    private function fingerprint(PHPWindConfig $config): ?string
    {
        $path = $config->inputCss;

        if (!is_file($path)) {
            return null;
        }

        $mtime = @filemtime($path);
        $size = @filesize($path);

        if ($mtime === false || $size === false) {
            return null;
        }

        return $mtime . '|' . $size;
    }

    private function read(): ?string
    {
        if (!is_file($this->stateFile)) {
            return null;
        }

        $data = @file_get_contents($this->stateFile);

        if ($data === false) {
            return null;
        }

        $decoded = json_decode($data, true);

        return is_array($decoded) && isset($decoded['fingerprint']) && is_string($decoded['fingerprint'])
            ? $decoded['fingerprint']
            : null;
    }

    private function write(string $fingerprint): void
    {
        $directory = dirname($this->stateFile);

        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        @file_put_contents($this->stateFile, json_encode(['fingerprint' => $fingerprint]), LOCK_EX);
    }
}
