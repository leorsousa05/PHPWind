<?php

declare(strict_types=1);

namespace PHPWind\Binary;

use PHPWind\Exception\BinaryDownloadException;

class Downloader
{
    public function __construct(
        private int $timeoutSeconds = 120,
        private bool $verifySsl = true
    ) {}

    /**
     * @deprecated Use BinaryManager::resolveBinaryPath() for version-aware binary resolution.
     */
    public function ensureBinaryInstalled(string $targetDirectory, string $version = 'v4.0.0'): string
    {
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $binaryPath = rtrim($targetDirectory, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getLocalBinaryFilename();

        if (file_exists($binaryPath)) {
            return realpath($binaryPath) ?: $binaryPath;
        }

        $this->download(PlatformResolver::getDownloadUrl($version), $binaryPath);

        return realpath($binaryPath) ?: $binaryPath;
    }

    /**
     * @throws BinaryDownloadException
     */
    public function download(string $url, string $destinationPath): void
    {
        $directory = dirname($destinationPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tmpPath = $destinationPath . '.part.' . uniqid('', true);
        $fp = fopen($tmpPath, 'w+');
        if ($fp === false) {
            throw new BinaryDownloadException("Could not open file for writing at {$tmpPath}");
        }

        try {
            $result = $this->executeRequest($url, $fp);
        } finally {
            fclose($fp);
        }

        if (!$result['success'] || $result['httpCode'] !== 200) {
            @unlink($tmpPath);

            throw new BinaryDownloadException(
                "Failed to download Tailwind CLI binary from {$url} (HTTP {$result['httpCode']})" . ($result['error'] !== '' ? ": {$result['error']}" : '')
            );
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($tmpPath, 0755);
        }

        if (!rename($tmpPath, $destinationPath)) {
            @unlink($tmpPath);

            throw new BinaryDownloadException("Could not move downloaded binary into place at {$destinationPath}");
        }
    }

    /**
     * @param resource $fp
     * @return array{success: bool, httpCode: int, error: string}
     */
    protected function executeRequest(string $url, $fp): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => (bool) $success,
            'httpCode' => (int) $httpCode,
            'error' => $error,
        ];
    }
}
