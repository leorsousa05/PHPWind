<?php

declare(strict_types=1);

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Command\CleanHandler;
use PHPWind\Config\PHPWindConfig;

class CleanCommand extends Command
{
    protected $signature = 'phpwind:clean {--all : Also remove compiled output CSS file}';
    protected $description = 'Clean downloaded Tailwind CLI binary and cached assets';

    public function handle(CleanHandler $handler): int
    {
        $config = PHPWindConfig::fromArray([
            'input_css' => config('phpwind.input_css', resource_path('css/app.css')),
            'output_css' => config('phpwind.output_css', public_path('css/app.css')),
            'binary_dir' => config('phpwind.binary_dir', base_path('vendor/bin/tailwind-cli')),
        ]);

        $handler->handle($config, (bool) $this->option('all'));
        $this->info('PHPWind cache and binary cleaned successfully.');

        return 0;
    }
}
