<?php

namespace PHPWind\Console;

class ArrowMenu
{
    public static function select(string $title, array $options, int $defaultIndex = 0): int
    {
        if (!function_exists('stream_isatty') || !stream_isatty(STDIN)) {
            return $defaultIndex;
        }

        $isWindows = PHP_OS_FAMILY === 'Windows';
        if (!$isWindows) {
            system('stty -icanon -echo 2>/dev/null');
        }

        $selectedIndex = $defaultIndex;
        $count = count($options);

        fwrite(STDOUT, "\e[?25l");
        fwrite(STDOUT, "  \e[36m?\e[0m \e[1m{$title}\e[0m \e[90m(Nav: ↑/↓ setas ou números 1-{$count})\e[0m\n");

        self::renderOptions($options, $selectedIndex, true);

        while (true) {
            $key = self::readKey($isWindows);

            if ($key === 'UP') {
                $selectedIndex = ($selectedIndex - 1 + $count) % $count;
                self::renderOptions($options, $selectedIndex, false);
            } elseif ($key === 'DOWN') {
                $selectedIndex = ($selectedIndex + 1) % $count;
                self::renderOptions($options, $selectedIndex, false);
            } elseif (is_numeric($key)) {
                $num = (int)$key - 1;
                if ($num >= 0 && $num < $count) {
                    $selectedIndex = $num;
                    self::renderOptions($options, $selectedIndex, false);
                    break;
                }
            } elseif ($key === 'ENTER') {
                break;
            }
        }

        if (!$isWindows) {
            system('stty sane 2>/dev/null');
        }
        fwrite(STDOUT, "\e[?25h");

        self::clearLines(count($options) + 1);
        fwrite(STDOUT, "  \e[32m✔\e[0m \e[1m{$title}\e[0m \e[36m" . $options[$selectedIndex] . "\e[0m\n");

        return $selectedIndex;
    }

    private static function renderOptions(array $options, int $selectedIndex, bool $isFirstRender): void
    {
        if (!$isFirstRender) {
            self::clearLines(count($options));
        }

        foreach ($options as $index => $option) {
            $num = $index + 1;
            if ($index === $selectedIndex) {
                fwrite(STDOUT, "  \e[36m❯ [{$num}] {$option}\e[0m\n");
            } else {
                fwrite(STDOUT, "    \e[90m[{$num}] {$option}\e[0m\n");
            }
        }
    }

    private static function clearLines(int $lines): void
    {
        for ($i = 0; $i < $lines; $i++) {
            fwrite(STDOUT, "\e[1A\e[2K");
        }
    }

    private static function readKey(bool $isWindows): string
    {
        if ($isWindows) {
            $line = trim(fgets(STDIN));
            if ($line === '') {
                return 'ENTER';
            }
            if ($line === 'A' || $line === 'w' || $line === 'W' || $line === 'up') {
                return 'UP';
            }
            if ($line === 'S' || $line === 's' || $line === 'down') {
                return 'DOWN';
            }
            if (is_numeric($line)) {
                return $line;
            }
            return 'ENTER';
        }

        $c = fread(STDIN, 1);
        if ($c === "\n" || $c === "\r") {
            return 'ENTER';
        }

        if ($c === "\e") {
            $c2 = fread(STDIN, 1);
            if ($c2 === "[") {
                $c3 = fread(STDIN, 1);
                if ($c3 === 'A') return 'UP';
                if ($c3 === 'B') return 'DOWN';
            }
        }

        if (is_numeric($c)) {
            return $c;
        }

        return '';
    }
}
