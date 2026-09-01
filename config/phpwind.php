<?php

declare(strict_types=1);

use PHPWind\Binary\PlatformResolver;
use PHPWind\Config\Env;

return [
    'input_css' => Env::get('PHPWIND_INPUT_CSS', 'resources/css/app.css'),
    'output_css' => Env::get('PHPWIND_OUTPUT_CSS', 'public/css/app.css'),
    'binary_dir' => Env::get('PHPWIND_BINARY_DIR', 'vendor/bin/tailwind-cli'),
    'version' => Env::get('PHPWIND_VERSION', PlatformResolver::DEFAULT_VERSION),
    'minify' => (bool) Env::get('PHPWIND_MINIFY', false),
    'watch' => (bool) Env::get('PHPWIND_WATCH', false),
];
