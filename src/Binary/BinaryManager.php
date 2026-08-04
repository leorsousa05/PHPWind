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
        if (!is_dir($this->binaryDir)) {
            mkdir($this->binaryDir, 0755, true);
        }

        $binaryPath = rtrim($this->binaryDir, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName($version);

        if (file_exists($binaryPath)) {
            return realpath($binaryPath) ?: $binaryPath;
        }

        $this->getDownloader()->download(PlatformResolver::getDownloadUrl($version), $binaryPath);

        return realpath($binaryPath) ?: $binaryPath;
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
