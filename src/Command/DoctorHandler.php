<?php

declare(strict_types=1);

namespace PHPWind\Command;

use PHPWind\Binary\PlatformResolver;
use PHPWind\Config\PHPWindConfig;

final class DoctorHandler
{
    /** @return array{exitCode:int, output:string} */
    public function diagnose(PHPWindConfig $config): array
    {
        $cachePath = rtrim($config->binaryDir, '/\\') . DIRECTORY_SEPARATOR . PlatformResolver::getVersionedBinaryName($config->version);
        $cacheState = !file_exists($cachePath) ? 'not installed' : (is_file($cachePath) && filesize($cachePath) > 0 && (PHP_OS_FAMILY === 'Windows' || is_executable($cachePath)) ? 'present/valid' : 'present/invalid');
        $checks = [
            'PHP version (>= 8.1)' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'Supported platform/architecture' => $this->supportedPlatform(),
            'cURL extension' => extension_loaded('curl'),
            'proc_open availability' => function_exists('proc_open') && !in_array('proc_open', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true),
            'Input CSS readable' => is_file($config->inputCss) && is_readable($config->inputCss),
            'Output directory writable' => is_dir(dirname($config->outputCss)) ? is_writable(dirname($config->outputCss)) : is_writable(dirname($config->outputCss) ?: '.'),
            'Binary cache directory writable' => is_dir($config->binaryDir) ? is_writable($config->binaryDir) : is_writable(dirname($config->binaryDir) ?: '.'),
        ];
        $lines = ['PHPWind doctor (offline; no download or binary execution)', 'Tailwind version: ' . $config->version, 'Input CSS: ' . $config->inputCss, 'Output CSS: ' . $config->outputCss, 'Binary cache: ' . $config->binaryDir, 'Binary cache entry: ' . $cacheState];
        foreach ($checks as $label => $ok) $lines[] = sprintf('[%s] %s', $ok ? 'OK' : 'FAIL', $label);
        $failed = in_array(false, $checks, true);
        $lines[] = $failed ? 'Summary: required checks failed.' : 'Summary: all required checks passed.';
        return ['exitCode' => $failed ? 1 : 0, 'output' => implode(PHP_EOL, $lines) . PHP_EOL];
    }

    private function supportedPlatform(): bool
    {
        try { PlatformResolver::getBinaryName(); return true; }
        catch (\Throwable) { return false; }
    }
}
