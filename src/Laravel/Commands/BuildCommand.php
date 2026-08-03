<?php

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;

class BuildCommand extends Command
{
    protected $signature = 'phpwind:build {--minify : Minify the CSS output}';
    protected $description = 'Build Tailwind CSS v4 using standalone CLI';

    public function handle(TailwindCompiler $compiler): int
    {
        $this->info('Building Tailwind CSS v4...');

        $config = PHPWindConfig::fromArray([
            'input_css' => config('phpwind.input_css', resource_path('css/app.css')),
            'output_css' => config('phpwind.output_css', public_path('css/app.css')),
            'binary_dir' => config('phpwind.binary_dir', base_path('vendor/bin/tailwind-cli')),
            'version' => config('phpwind.version', 'v4.0.0'),
            'minify' => $this->option('minify') || config('phpwind.minify', false),
            'watch' => false
        ]);

        $exitCode = $compiler->compile($config);

        if ($exitCode === 0) {
            $this->info('Tailwind CSS v4 build completed successfully.');
        } else {
            $this->error('Tailwind CSS v4 build failed.');
        }

        return $exitCode;
    }
}
