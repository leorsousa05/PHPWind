<?php

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;

class WatchCommand extends Command
{
    protected $signature = 'phpwind:watch';
    protected $description = 'Start Tailwind CSS v4 watcher using standalone CLI';

    public function handle(TailwindCompiler $compiler): int
    {
        $this->info('Starting Tailwind CSS v4 watcher...');

        $config = PHPWindConfig::fromArray([
            'input_css' => config('phpwind.input_css', resource_path('css/app.css')),
            'output_css' => config('phpwind.output_css', public_path('css/app.css')),
            'binary_dir' => config('phpwind.binary_dir', base_path('vendor/bin/tailwind-cli')),
            'version' => config('phpwind.version', 'v4.0.0'),
            'minify' => false,
            'watch' => true
        ]);

        return $compiler->compile($config);
    }
}
