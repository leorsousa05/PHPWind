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
        $content = @file_get_contents($url);

        if ($content === false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($content)) {
                throw new RuntimeException("Failed to download Tailwind CLI binary from {$url}");
            }
        }

        file_put_contents($binaryPath, $content);

        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($binaryPath, 0755);
        }

        return realpath($binaryPath);
    }
}
