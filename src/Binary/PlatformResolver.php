<?php

declare(strict_types=1);

namespace PHPWind\Binary;

class PlatformResolver
{
    public const DEFAULT_VERSION = 'v4.0.0';

    public static function getBinaryName(?string $os = null, ?string $architecture = null): string
    {
        $os = strtolower($os ?? PHP_OS_FAMILY);
        $arch = strtolower($architecture ?? php_uname('m'));
        $isArm = in_array($arch, ['arm64', 'aarch64'], true);
        $isX64 = in_array($arch, ['x86_64', 'amd64', 'x64'], true);
        if (!$isArm && !$isX64) {
            throw new \InvalidArgumentException("Unsupported Tailwind CLI architecture: {$arch}");
        }

        if (in_array($os, ['windows', 'winnt'], true)) {
            return $isArm ? 'tailwindcss-windows-arm64.exe' : 'tailwindcss-windows-x64.exe';
        }

        if (in_array($os, ['darwin', 'macos'], true)) {
            return $isArm ? 'tailwindcss-macos-arm64' : 'tailwindcss-macos-x64';
        }

        if ($os !== 'linux') {
            throw new \InvalidArgumentException("Unsupported Tailwind CLI operating system: {$os}");
        }
        return $isArm ? 'tailwindcss-linux-arm64' : 'tailwindcss-linux-x64';
    }

    public static function getDownloadUrl(string $version = self::DEFAULT_VERSION): string
    {
        $cleanVersion = preg_replace('/^v/', '', $version);
        if (!is_string($cleanVersion) || !preg_match('/^(0|[1-9]\\d*)\\.(0|[1-9]\\d*)\\.(0|[1-9]\\d*)(?:-(?:0|[1-9]\\d*|[0-9A-Za-z-]*[A-Za-z-][0-9A-Za-z-]*)(?:\\.(?:0|[1-9]\\d*|[0-9A-Za-z-]*[A-Za-z-][0-9A-Za-z-]*))*)?(?:\\+[0-9A-Za-z.-]+)?$/', $cleanVersion)) {
            throw new \InvalidArgumentException("Invalid Tailwind semantic version: {$version}");
        }
        $binary = self::getBinaryName();
        $repo = str_starts_with($cleanVersion, '3.') ? 'tailwindcss/tailwindcss-cli' : 'tailwindcss/tailwindcss';

        return "https://github.com/{$repo}/releases/download/v{$cleanVersion}/{$binary}";
    }

    public static function getLocalBinaryFilename(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'tailwind.exe' : 'tailwind';
    }

    public static function getVersionedBinaryName(string $version): string
    {
        $version = preg_replace('/^v/', '', $version) ?? $version;
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';

        return "tailwind-v{$version}{$extension}";
    }
}
