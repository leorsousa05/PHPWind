<?php

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Command\InitHandler;
use PHPWind\Config\PHPWindConfig;

class InitCommand extends Command
{
    protected $signature = 'phpwind:init';
    protected $description = 'Initialize default Tailwind CSS input file for PHPWind';

    public function handle(InitHandler $handler): int
    {
        $config = PHPWindConfig::fromArray([
            'input_css' => config('phpwind.input_css', resource_path('css/app.css')),
            'output_css' => config('phpwind.output_css', public_path('css/app.css')),
        ]);

        $handler->handle($config);
        $this->info("PHPWind input CSS initialized at {$config->inputCss}");

        return 0;
    }
}
