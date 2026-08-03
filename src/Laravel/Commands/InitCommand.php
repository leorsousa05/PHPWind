<?php

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Command\InitHandler;
use PHPWind\Config\PHPWindConfig;

class InitCommand extends Command
{
    protected $signature = 'phpwind:init {--input= : Path to input CSS file} {--output= : Path to output CSS file} {--version= : Tailwind version}';
    protected $description = 'Interactively initialize Tailwind CSS configuration and assets for PHPWind';

    public function handle(InitHandler $handler): int
    {
        $this->info('🌬️ PHPWind Interactive Setup Wizard');

        $input = $this->option('input') ?: $this->ask('Input CSS path', config('phpwind.input_css', resource_path('css/app.css')));
        $output = $this->option('output') ?: $this->ask('Output CSS path', config('phpwind.output_css', public_path('css/app.css')));
        $version = $this->option('version') ?: $this->ask('Tailwind CSS version', config('phpwind.version', 'v4.0.0'));

        $config = new PHPWindConfig(
            inputCss: $input,
            outputCss: $output,
            version: $version
        );

        $handler->handle($config);
        $this->info("✓ PHPWind initialized input CSS at {$input}");

        return 0;
    }
}
