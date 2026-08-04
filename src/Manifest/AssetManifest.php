<?php

declare(strict_types=1);

namespace PHPWind\Manifest;

use PHPWind\Exception\AssetManifestException;

class AssetManifest
{
    /**
     * @param array<string, AssetEntry> $entries
     */
    public function __construct(private array $entries = []) {}

    public static function fromArray(array $data): self
    {
        $entries = [];

        foreach ($data as $logicalPath => $entryData) {
            if (is_array($entryData)) {
                $entries[$logicalPath] = new AssetEntry(
                    path: $entryData['path'] ?? (string) $logicalPath,
                    hash: $entryData['hash'] ?? ''
                );
            } elseif (is_string($entryData)) {
                // Support flat schema: "css/app.css" => "a1b2c3d4"
                $entries[$logicalPath] = new AssetEntry(
                    path: (string) $logicalPath,
                    hash: $entryData
                );
            }
        }

        return new self($entries);
    }

    public static function read(string $path): self
    {
        if (!file_exists($path)) {
            throw new AssetManifestException("Manifest file not found: {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new AssetManifestException("Could not read manifest file: {$path}");
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new AssetManifestException("Invalid manifest JSON in: {$path}");
        }

        return self::fromArray($data);
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this->entries as $logicalPath => $entry) {
            $data[$logicalPath] = [
                'path' => $entry->path,
                'hash' => $entry->hash,
            ];
        }

        return $data;
    }

    public function write(string $path): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $json = json_encode($this->toArray(), JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new AssetManifestException("Could not encode manifest to JSON.");
        }

        $result = file_put_contents($path, $json, LOCK_EX);
        if ($result === false) {
            throw new AssetManifestException("Could not write manifest file: {$path}");
        }
    }

    public function get(string $logicalPath): ?AssetEntry
    {
        return $this->entries[$logicalPath] ?? null;
    }

    public function set(string $logicalPath, AssetEntry $entry): self
    {
        $this->entries[$logicalPath] = $entry;

        return $this;
    }

    /**
     * @param array<int, string> $logicalPaths
     */
    public static function generate(string $publicDir, array $logicalPaths = ['css/app.css']): self
    {
        $entries = [];

        foreach ($logicalPaths as $logicalPath) {
            $path = ltrim($logicalPath, '/');
            $fullPath = rtrim($publicDir, '/\\') . DIRECTORY_SEPARATOR . $path;

            $hash = file_exists($fullPath) ? substr(md5_file($fullPath) ?: '', 0, 8) : '';
            $entries[$logicalPath] = new AssetEntry(path: $path, hash: $hash);
        }

        return new self($entries);
    }
}
