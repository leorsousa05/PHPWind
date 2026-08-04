<?php

declare(strict_types=1);

namespace PHPWind\Helper;

use PHPWind\Manifest\AssetManifest;

class AssetHelper
{
    public static function css(string $path = 'css/app.css', bool $versioned = true): string
    {
        $href = '/' . ltrim($path, '/');
        $publicPath = getcwd() . '/public/' . ltrim($path, '/');

        if ($versioned && file_exists($publicPath)) {
            $hash = substr(md5_file($publicPath) ?: '', 0, 8);
            $href .= '?v=' . $hash;
        }

        return '<link rel="stylesheet" href="' . htmlspecialchars($href) . '">';
    }

    public static function cssFromManifest(
        AssetManifest $manifest,
        string $path = 'css/app.css',
        bool $versioned = true
    ): string {
        $href = '/' . ltrim($path, '/');

        if ($versioned) {
            $entry = $manifest->get($path);
            if ($entry !== null && $entry->hash !== '') {
                $href .= '?v=' . $entry->hash;
            }
        }

        return '<link rel="stylesheet" href="' . htmlspecialchars($href) . '">';
    }
}
