<?php

declare(strict_types=1);

use PHPWind\Helper\AssetHelper;
use PHPWind\Manifest\AssetManifest;

if (!function_exists('phpwind_css')) {
    function phpwind_css(string $path = 'css/app.css', bool $versioned = true, ?string $publicDir = null): string
    {
        return AssetHelper::css($path, $versioned, $publicDir);
    }
}

if (!function_exists('phpwind_manifest_css')) {
    function phpwind_manifest_css(AssetManifest $manifest, string $path = 'css/app.css', bool $versioned = true): string
    {
        return AssetHelper::cssFromManifest($manifest, $path, $versioned);
    }
}
