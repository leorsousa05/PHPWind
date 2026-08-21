<?php

declare(strict_types=1);

namespace PHPWind\Binary;

use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\BinaryExecutionException;

class Runner
{
    public function run(string $binaryPath, PHPWindConfig $config): int
    {
        return $this->runResult($binaryPath, $config)->exitCode;
    }

    public function runResult(string $binaryPath, PHPWindConfig $config): ProcessResult
    {
        if (str_contains($binaryPath, "\0")) {
            throw new BinaryExecutionException("Invalid binary path containing null bytes");
        }

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

        // Watch mode streams directly to the parent process so long-running
        // output is not buffered in memory.
        if ($config->watch) {
            $descriptors = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR
            ];

            $process = proc_open($cmdString, $descriptors, $pipes);

            if (!is_resource($process)) {
                throw new BinaryExecutionException("Could not execute process: {$cmdString}");
            }

            return new ProcessResult(exitCode: proc_close($process));
        }

        [$stdout, $stderr, $exitCode] = $this->runCaptured($cmdString);

        return new ProcessResult(
            exitCode: $exitCode,
            stdout: $stdout,
            stderr: $stderr
        );
    }

    /**
     * Executes a command capturing stdout and stderr concurrently to avoid
     * pipe-buffer deadlocks.
     *
     * @return array{0: string, 1: string, 2: int}
     */
    private function runCaptured(string $cmdString): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmdString, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new BinaryExecutionException("Could not execute process: {$cmdString}");
        }

        fclose($pipes[0]);

        $stdout = '';
        $stderr = '';
        $streams = [$pipes[1], $pipes[2]];

        while (count($streams) > 0) {
            $read = $streams;
            $write = null;
            $except = null;

            if (@stream_select($read, $write, $except, 0, 200000) === false) {
                break;
            }

            foreach ($read as $stream) {
                $chunk = fread($stream, 8192);

                if ($chunk === '' || $chunk === false) {
                    if (feof($stream)) {
                        fclose($stream);
                        $index = array_search($stream, $streams, true);
                        if ($index !== false) {
                            unset($streams[$index]);
                        }
                    }
                } elseif ($stream === $pipes[1]) {
                    $stdout .= $chunk;
                } else {
                    $stderr .= $chunk;
                }
            }
        }

        $exitCode = proc_close($process);

        return [$stdout, $stderr, $exitCode];
    }
}
