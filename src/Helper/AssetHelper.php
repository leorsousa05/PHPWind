<?php

namespace PHPWind\Helper;

class AssetHelper
{
    public static function css(string $path = 'css/app.css', bool $versioned = true): string
    {
        $href = '/' . ltrim($path, '/');
        $publicPath = getcwd() . '/public/' . ltrim($path, '/');

        if ($versioned && file_exists($publicPath)) {
            $hash = substr(md5_file($publicPath), 0, 8);
            $href .= '?v=' . $hash;
        }

        return '<link rel="stylesheet" href="' . htmlspecialchars($href) . '">';
    }
}
