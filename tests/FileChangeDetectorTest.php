<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\ChangeDetection\FileChangeDetector;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class FileChangeDetectorTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_change_detector_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testHasChangedIsTrueOnFirstRun(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $detector = new FileChangeDetector($this->tempDir . '/state.json');

        $this->assertTrue($detector->hasChanged(new PHPWindConfig(inputCss: $input)));
    }

    public function testHasChangedIsFalseWhenUnchangedAfterRecord(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);
        $detector = new FileChangeDetector($this->tempDir . '/state.json');

        $detector->record($config);
        $this->assertFalse($detector->hasChanged($config));
    }

    public function testHasChangedIsTrueWhenFileModified(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $config = new PHPWindConfig(inputCss: $input);
        $detector = new FileChangeDetector($this->tempDir . '/state.json');

        $detector->record($config);
        touch($input, time() + 10);
        clearstatcache(true, $input);

        $this->assertTrue($detector->hasChanged($config));
    }

    public function testHasChangedIsTrueWhenInputFileMissing(): void
    {
        $detector = new FileChangeDetector($this->tempDir . '/state.json');

        $this->assertTrue($detector->hasChanged(new PHPWindConfig(inputCss: $this->tempDir . '/missing.css')));
    }

    public function testRecordPersistsStateAcrossDetectorInstances(): void
    {
        $input = $this->tempDir . '/app.css';
        file_put_contents($input, 'body{}');
        $stateFile = $this->tempDir . '/.cache/state.json';
        $config = new PHPWindConfig(inputCss: $input);

        $first = new FileChangeDetector($stateFile);
        $first->record($config);

        // A fresh detector reading the same state file sees no change.
        $second = new FileChangeDetector($stateFile);
        $this->assertFalse($second->hasChanged($config));
        $this->assertFileExists($stateFile);
    }
}
