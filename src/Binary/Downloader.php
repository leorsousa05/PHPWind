<?php

namespace PHPWind\Binary;

use RuntimeException;

class Downloader
{
    public function ensureBinaryInstalled(string $targetDirectory, string $version = 'v4.0.0'): string
    {
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $binaryPath = rtrim($targetDirectory, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();

        if (file_exists($binaryPath)) {
            return realpath($binaryPath);
        }

        $url = PlatformResolver::getDownloadUrl($version);

        $fp = fopen($binaryPath, 'w+');
        if ($fp === false) {
            throw new RuntimeException("Could not open file for writing at {$binaryPath}");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode !== 200) {
            if (file_exists($binaryPath)) {
                unlink($binaryPath);
            }
            throw new RuntimeException("Failed to download Tailwind CLI binary from {$url} (HTTP {$httpCode})");
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($binaryPath, 0755);
        }

        return realpath($binaryPath);
    }
}
