<?php

namespace PHPWind\Binary;

class PlatformResolver
{
    public static function getBinaryName(): string
    {
        $os = strtolower(PHP_OS_FAMILY);
        $arch = strtolower(php_uname('m'));

        $isArm = str_contains($arch, 'arm') || str_contains($arch, 'aarch64');

        if ($os === 'windows') {
            return $isArm ? 'tailwindcss-windows-arm64.exe' : 'tailwindcss-windows-x64.exe';
        }

        if ($os === 'darwin') {
            return $isArm ? 'tailwindcss-macos-arm64' : 'tailwindcss-macos-x64';
        }

        return $isArm ? 'tailwindcss-linux-arm64' : 'tailwindcss-linux-x64';
    }

    public static function getDownloadUrl(string $version = 'v4.0.0'): string
    {
        $version = ltrim($version, 'v');
        $binary = self::getBinaryName();
        return "https://github.com/tailwindcss/tailwindcss/releases/download/v{$version}/{$binary}";
    }

    public static function getLocalBinaryFilename(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'tailwind.exe' : 'tailwind';
    }
}
