<?php

declare(strict_types=1);

namespace PHPWind\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use PHPWind\Laravel\Commands\BuildCommand;
use PHPWind\Laravel\Commands\CleanCommand;
use PHPWind\Laravel\Commands\InitCommand;
use PHPWind\Laravel\Commands\WatchCommand;

class PHPWindServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/phpwind.php', 'phpwind'
        );
    }

    public function boot(): void
    {
        $this->resolveRelativeConfigPaths();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/phpwind.php' => config_path('phpwind.php'),
            ], 'phpwind-config');

            $this->commands([
                BuildCommand::class,
                WatchCommand::class,
                InitCommand::class,
                CleanCommand::class,
            ]);
        }

        Blade::directive('phpwind', function ($expression) {
            $path = $expression ?: "'css/app.css'";
            return "<?php echo \\PHPWind\\Helper\\AssetHelper::css({$path}); ?>";
        });
    }

    /**
     * The package config uses framework-agnostic relative paths. When a value
     * still matches a shipped default, resolve it to a Laravel absolute path so
     * published/packaged usage keeps the original behavior. User-customized
     * values are left untouched.
     */
    private function resolveRelativeConfigPaths(): void
    {
        $config = $this->app['config']->get('phpwind', []);

        $defaults = [
            'input_css' => ['resources/css/app.css', resource_path('css/app.css')],
            'output_css' => ['public/css/app.css', public_path('css/app.css')],
            'binary_dir' => ['vendor/bin/tailwind-cli', base_path('vendor/bin/tailwind-cli')],
        ];

        foreach ($defaults as $key => [$relative, $absolute]) {
            if (($config[$key] ?? null) === $relative) {
                $config[$key] = $absolute;
            }
        }

        $this->app['config']->set('phpwind', $config);
    }
}
