<?php

namespace PHPWind\Binary;

use PHPWind\Config\PHPWindConfig;
use RuntimeException;

class Runner
{
    public function run(string $binaryPath, PHPWindConfig $config): int
    {
        $command = [
            escapeshellcmd($binaryPath),
            '-i', escapeshellarg($config->inputCss),
            '-o', escapeshellarg($config->outputCss)
        ];

        if ($config->minify) {
            $command[] = '--minify';
        }

        if ($config->watch) {
            $command[] = '--watch';
        }

        $cmdString = implode(' ', $command);

        $descriptors = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR
        ];

        $process = proc_open($cmdString, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException("Could not execute process: {$cmdString}");
        }

        return proc_close($process);
    }
}
