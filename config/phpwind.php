<?php

return [
    'input_css' => resource_path('css/app.css'),
    'output_css' => public_path('css/app.css'),
    'binary_dir' => base_path('vendor/bin/tailwind-cli'),
    'version' => 'v4.0.0',
    'minify' => env('PHPWIND_MINIFY', false),
];
