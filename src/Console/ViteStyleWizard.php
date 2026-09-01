<?php

declare(strict_types=1);

namespace PHPWind\Console;

use PHPWind\Binary\PlatformResolver;

class ViteStyleWizard
{
    private const PACKAGE_VERSION = '1.7.0';

    public static function run(array $args): array
    {
        $noInteraction = in_array('--no-interaction', $args) || in_array('-n', $args);

        if ($noInteraction || !function_exists('stream_isatty') || !stream_isatty(STDIN)) {
            return [
                'input' => 'resources/css/app.css',
                'output' => 'public/css/app.css',
                'version' => PlatformResolver::DEFAULT_VERSION,
                'preset' => 'vanilla',
                'create_sample' => false
            ];
        }

        self::write("\n  \e[1;36m🌬️  PHPWind Interactive Setup Wizard\e[0m \e[90mv" . self::PACKAGE_VERSION . "\e[0m\n\n");

        self::write("  \e[36m?\e[0m \e[1mSelect framework preset:\e[0m\n");
        self::write("    \e[36m1)\e[0m Vanilla PHP / Custom Framework \e[90m(Default)\e[0m\n");
        self::write("    \e[90m2)\e[0m Laravel Framework\n");
        self::write("    \e[90m3)\e[0m Symfony Framework\n");
        $presetChoice = self::prompt("  \e[90mSelect option [1-3] (1):\e[0m ", '1');

        $preset = match ($presetChoice) {
            '2' => 'laravel',
            '3' => 'symfony',
            default => 'vanilla',
        };

        self::write("\n  \e[36m?\e[0m \e[1mSelect Tailwind CSS version:\e[0m\n");
        self::write("    \e[36m1)\e[0m v4.0.0 \e[90m(Tailwind v4 - Latest Standalone)\e[0m\n");
        self::write("    \e[90m2)\e[0m v3.4.17 \e[90m(Tailwind v3.x)\e[0m\n");
        $versionChoice = self::prompt("  \e[90mSelect version [1-2] (1):\e[0m ", '1');

        $version = $versionChoice === '2' ? 'v3.4.17' : PlatformResolver::DEFAULT_VERSION;

        $defaultInput = 'resources/css/app.css';
        $defaultOutput = 'public/css/app.css';

        self::write("\n");
        $inputPath = self::prompt("  \e[36m?\e[0m \e[1mInput CSS path\e[0m \e[90m({$defaultInput}):\e[0m ", $defaultInput);
        $outputPath = self::prompt("  \e[36m?\e[0m \e[1mOutput CSS path\e[0m \e[90m({$defaultOutput}):\e[0m ", $defaultOutput);

        $createSample = false;
        if ($preset === 'vanilla') {
            $sampleChoice = self::prompt("\n  \e[36m?\e[0m \e[1mCreate sample index.php file?\e[0m \e[90m(Y/n):\e[0m ", 'y');
            $createSample = strtolower($sampleChoice) !== 'n';
        }

        self::write("\n  \e[32m✔\e[0m \e[1mScaffolding PHPWind project setup...\e[0m\n\n");

        return [
            'input' => $inputPath,
            'output' => $outputPath,
            'version' => $version,
            'preset' => $preset,
            'create_sample' => $createSample
        ];
    }

    public static function printNextSteps(string $input, string $output, string $preset): void
    {
        self::write("  \e[32mDone.\e[0m Now run:\n\n");
        self::write("    \e[36mvendor/bin/phpwind watch -i {$input} -o {$output}\e[0m\n\n");
        if ($preset === 'vanilla') {
            self::write("    \e[90mphp -S localhost:8000 -t public\e[0m\n\n");
        }
    }

    private static function prompt(string $message, string $default): string
    {
        fwrite(STDOUT, $message);
        $input = trim(fgets(STDIN));
        return $input !== '' ? $input : $default;
    }

    private static function write(string $message): void
    {
        fwrite(STDOUT, $message);
    }
}
