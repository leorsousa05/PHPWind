<?php

declare(strict_types=1);

namespace PHPWind\Laravel\Commands;

use Illuminate\Console\Command;
use PHPWind\Binary\PlatformResolver;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Command\BuildOutputFormatter;

class BuildCommand extends Command
{
    protected $signature = 'phpwind:build {--minify : Minify the CSS output}';
    protected $description = 'Build Tailwind CSS v4 using standalone CLI';

    public function handle(TailwindCompiler $compiler): int
    {
        $config = PHPWindConfig::fromArray([
            'input_css' => config('phpwind.input_css', resource_path('css/app.css')),
            'output_css' => config('phpwind.output_css', public_path('css/app.css')),
            'binary_dir' => config('phpwind.binary_dir', base_path('vendor/bin/tailwind-cli')),
            'version' => config('phpwind.version', PlatformResolver::DEFAULT_VERSION),
            'minify' => $this->option('minify') || config('phpwind.minify', false),
            'watch' => false,
            'download_timeout' => config('phpwind.download_timeout', 120),
            'verify_ssl' => config('phpwind.verify_ssl', true)
        ]);

        try { $result = $compiler->compileResult($config); }
        catch (\Throwable $exception) {
            $this->error(BuildOutputFormatter::exception($exception));
            return 1;
        }
        $exitCode = $result->exitCode;

        if ($exitCode === 0) {
            $this->info(BuildOutputFormatter::success());
        } else {
            $this->error(BuildOutputFormatter::failure($result));
        }

        return $exitCode;
    }
}
