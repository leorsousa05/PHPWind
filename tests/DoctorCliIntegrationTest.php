<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

final class DoctorCliIntegrationTest extends TestCase
{
    use RemovesTempDirectories;

    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_doctor_' . uniqid();
        mkdir($this->projectDir, 0755, true);
        mkdir($this->projectDir . '/resources/css', 0755, true);
        mkdir($this->projectDir . '/public/css', 0755, true);
        mkdir($this->projectDir . '/config', 0755, true);
        mkdir($this->projectDir . '/var', 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->projectDir)) {
            $this->removeDirectory($this->projectDir);
        }
    }

    public function testDoctorSucceedsForValidProjectWithoutCreatingCacheOrOutput(): void
    {
        file_put_contents($this->projectDir . '/resources/css/app.css', '/* input */');
        $this->writeConfig();

        [$exitCode, $output] = $this->runDoctor();

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('[OK] Input CSS readable', $output);
        $this->assertStringContainsString('[OK] Output directory writable', $output);
        $this->assertStringContainsString('Binary cache entry: not installed', $output);
        $this->assertStringContainsString('Summary: all required checks passed.', $output);
        $this->assertDirectoryDoesNotExist($this->projectDir . '/var/phpwind');
        $this->assertFileDoesNotExist($this->projectDir . '/public/css/app.css');
        $this->assertSame(['app.css'], array_values(array_diff(scandir($this->projectDir . '/resources/css') ?: [], ['.', '..'])));
    }

    public function testDoctorFailsActionablyForMissingInputWithoutCreatingCacheOrBinary(): void
    {
        $this->writeConfig();

        [$exitCode, $output] = $this->runDoctor();

        $this->assertSame(1, $exitCode, $output);
        $this->assertStringContainsString('[FAIL] Input CSS readable', $output);
        $this->assertStringContainsString('Input CSS: resources/css/app.css', $output);
        $this->assertStringContainsString('Summary: required checks failed.', $output);
        $this->assertDirectoryDoesNotExist($this->projectDir . '/var/phpwind');
        $this->assertFileDoesNotExist($this->projectDir . '/public/css/app.css');
        $this->assertDirectoryDoesNotExist($this->projectDir . '/bin');
    }

    private function writeConfig(): void
    {
        file_put_contents($this->projectDir . '/config/phpwind.php', "<?php\nreturn ['binary_dir' => 'var/phpwind'];\n");
    }

    /** @return array{int, string} */
    private function runDoctor(): array
    {
        $repository = dirname(__DIR__);
        $autoload = $repository . '/vendor/autoload.php';
        $script = $repository . '/bin/phpwind';
        $command = [PHP_BINARY, '-d', 'auto_prepend_file=' . $autoload, $script, 'doctor'];
        $pipes = [];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $this->projectDir);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout . $stderr];
    }
}
