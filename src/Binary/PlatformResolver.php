<?php

declare(strict_types=1);

namespace PHPWind\Binary;

class PlatformResolver
{
    public static function getBinaryName(string $version = 'v4.0.0'): string
    {
        $os = strtolower(PHP_OS_FAMILY);
        $arch = strtolower(php_uname('m'));
        $isArm = str_contains($arch, 'arm') || str_contains($arch, 'aarch64');
        $isV3 = str_starts_with(ltrim($version, 'v'), '3.');

        if ($os === 'windows') {
            if ($isV3) {
                return $isArm ? 'tailwindcss-windows-arm64.exe' : 'tailwindcss-windows-x64.exe';
            }
            return $isArm ? 'tailwindcss-windows-arm64.exe' : 'tailwindcss-windows-x64.exe';
        }

        if ($os === 'darwin') {
            if ($isV3) {
                return $isArm ? 'tailwindcss-macos-arm64' : 'tailwindcss-macos-x64';
            }
            return $isArm ? 'tailwindcss-macos-arm64' : 'tailwindcss-macos-x64';
        }

        if ($isV3) {
            return $isArm ? 'tailwindcss-linux-arm64' : 'tailwindcss-linux-x64';
        }

        return $isArm ? 'tailwindcss-linux-arm64' : 'tailwindcss-linux-x64';
    }

    public static function getDownloadUrl(string $version = 'v4.0.0'): string
    {
        $cleanVersion = ltrim($version, 'v');
        $binary = self::getBinaryName($version);
        $repo = str_starts_with($cleanVersion, '3.') ? 'tailwindcss/tailwindcss-cli' : 'tailwindcss/tailwindcss';

        return "https://github.com/{$repo}/releases/download/v{$cleanVersion}/{$binary}";
    }

    public static function getLocalBinaryFilename(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'tailwind.exe' : 'tailwind';
    }

    public static function getVersionedBinaryName(string $version): string
    {
        $version = ltrim($version, 'v');
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';

        return "tailwind-v{$version}{$extension}";
    }
}
