<?php

use PHPWind\Helper\AssetHelper;

if (!function_exists('phpwind_css')) {
    function phpwind_css(string $path = 'css/app.css', bool $versioned = true): string
    {
        return AssetHelper::css($path, $versioned);
    }
}
