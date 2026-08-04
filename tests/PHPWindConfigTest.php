<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\InvalidConfigurationException;

class PHPWindConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new PHPWindConfig();
        $this->assertEquals('resources/css/app.css', $config->inputCss);
        $this->assertEquals('public/css/app.css', $config->outputCss);
        $this->assertFalse($config->minify);
        $this->assertFalse($config->watch);
    }

    public function testFromArray(): void
    {
        $config = PHPWindConfig::fromArray([
            'input_css' => 'src/input.css',
            'output_css' => 'dist/output.css',
            'minify' => true,
        ]);

        $this->assertEquals('src/input.css', $config->inputCss);
        $this->assertEquals('dist/output.css', $config->outputCss);
        $this->assertTrue($config->minify);
    }

    public function testValidateAcceptsValidConfig(): void
    {
        $config = new PHPWindConfig();
        $config->validate();

        $this->assertTrue(true);
    }

    public function testValidateAcceptsVersionWithoutPrefix(): void
    {
        $config = new PHPWindConfig(version: '4.0.0');
        $config->validate();

        $this->assertTrue(true);
    }

    public function testValidateRejectsEmptyInputCss(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('inputCss cannot be empty.');

        $config = new PHPWindConfig(inputCss: '  ');
        $config->validate();
    }

    public function testValidateRejectsEmptyOutputCss(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('outputCss cannot be empty.');

        $config = new PHPWindConfig(outputCss: '');
        $config->validate();
    }

    public function testValidateRejectsEmptyBinaryDir(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('binaryDir cannot be empty.');

        $config = new PHPWindConfig(binaryDir: '');
        $config->validate();
    }

    public function testValidateRejectsInvalidVersion(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('version must be a valid semantic version');

        $config = new PHPWindConfig(version: 'not-a-version');
        $config->validate();
    }
}
