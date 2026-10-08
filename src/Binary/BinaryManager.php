<?php

declare(strict_types=1);

namespace PHPWind\Binary;

use PHPWind\Exception\BinaryDownloadException;

class BinaryManager
{
    public function __construct(
        private string $binaryDir,
        private ?Downloader $downloader = null
    ) {}

    /**
     * @throws BinaryDownloadException
     */
    public function resolveBinaryPath(string $version): string
    {
        PlatformResolver::getDownloadUrl($version); // Validate version and platform before touching the cache.
        if (!is_dir($this->binaryDir)) {
            if (!@mkdir($this->binaryDir, 0755, true) && !is_dir($this->binaryDir)) {
                throw new BinaryDownloadException("Could not create binary cache directory at {$this->binaryDir}");
            }
        }

        $binaryPath = rtrim($this->binaryDir, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName($version);
        $lock = fopen($binaryPath . '.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new BinaryDownloadException("Could not lock binary cache entry at {$binaryPath}");
        }
        try {
            if (is_file($binaryPath) && $this->isValidBinary($binaryPath)) {
                return realpath($binaryPath) ?: $binaryPath;
            }
            if (file_exists($binaryPath) && !@unlink($binaryPath)) {
                throw new BinaryDownloadException("Invalid cached Tailwind CLI binary could not be removed at {$binaryPath}");
            }
            $this->getDownloader()->download(PlatformResolver::getDownloadUrl($version), $binaryPath);
            if (!$this->isValidBinary($binaryPath)) {
                @unlink($binaryPath);
                throw new BinaryDownloadException("Downloaded Tailwind CLI binary is empty or not executable at {$binaryPath}");
            }
            return realpath($binaryPath) ?: $binaryPath;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function isValidBinary(string $path): bool
    {
        return filesize($path) > 0 && (PHP_OS_FAMILY === 'Windows' || is_executable($path));
    }

    public function clearCachedBinary(?string $version = null): bool
    {
        $removed = false;
        $binaryDir = rtrim($this->binaryDir, '/\\');

        if (!is_dir($binaryDir)) {
            return false;
        }

        $genericBinary = $binaryDir . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();
        if (file_exists($genericBinary) && is_file($genericBinary)) {
            unlink($genericBinary);
            $removed = true;
        }

        if ($version !== null) {
            $versionedBinary = $binaryDir . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName($version);
            if (file_exists($versionedBinary) && is_file($versionedBinary)) {
                unlink($versionedBinary);
                $removed = true;
            }

            return $removed;
        }

        foreach (glob($binaryDir . DIRECTORY_SEPARATOR . 'tailwind-v*') as $file) {
            if (is_file($file)) {
                unlink($file);
                $removed = true;
            }
        }

        return $removed;
    }

    public function getDownloader(): Downloader
    {
        return $this->downloader ?? new Downloader();
    }
}
