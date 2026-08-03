<?php

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
}
