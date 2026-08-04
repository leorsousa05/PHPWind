<?php

declare(strict_types=1);

namespace PHPWind\Command;

use PHPWind\Config\PHPWindConfig;

class InitHandler
{
    public function handle(PHPWindConfig $config, bool $createSampleHtml = false): bool
    {
        $config->validate();

        $inputDir = dirname($config->inputCss);
        if (!is_dir($inputDir)) {
            mkdir($inputDir, 0755, true);
        }

        if (!file_exists($config->inputCss)) {
            $directive = str_starts_with(ltrim($config->version, 'v'), '3.')
                ? "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n"
                : "@import \"tailwindcss\";\n";
            file_put_contents($config->inputCss, $directive);
        }

        if ($createSampleHtml) {
            $htmlPath = getcwd() . DIRECTORY_SEPARATOR . 'index.php';
            if (!file_exists($htmlPath)) {
                $sampleHtml = <<<HTML
<?php require_once __DIR__ . '/vendor/autoload.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPWind App</title>
    <?php echo phpwind_css('{$config->outputCss}'); ?>
</head>
<body class="bg-slate-900 text-white flex items-center justify-center min-h-screen">
    <div class="p-8 bg-slate-800 rounded-xl shadow-2xl text-center">
        <h1 class="text-3xl font-bold text-sky-400">Welcome to PHPWind</h1>
        <p class="text-slate-300 mt-2">Tailwind CSS v{$config->version} initialized successfully!</p>
    </div>
</body>
</html>
HTML;
                file_put_contents($htmlPath, $sampleHtml);
            }
        }

        return true;
    }
}
