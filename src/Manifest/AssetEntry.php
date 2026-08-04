<?php

declare(strict_types=1);

namespace PHPWind\Manifest;

class AssetEntry
{
    public function __construct(
        public readonly string $path,
        public readonly string $hash
    ) {}

    public function url(string $basePath = '/'): string
    {
        $basePath = rtrim($basePath, '/');
        $path = '/' . ltrim($this->path, '/');

        return $basePath . $path . '?v=' . $this->hash;
    }
}
