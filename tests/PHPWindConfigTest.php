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
        $this->assertSame(120, $config->downloadTimeout);
        $this->assertTrue($config->verifySsl);
    }

    public function testDownloadSettingsRoundTripAndRejectInvalidTimeout(): void
    {
        $config = PHPWindConfig::fromArray(['download_timeout' => '45', 'verify_ssl' => 'false']);
        $this->assertSame(45, $config->downloadTimeout);
        $this->assertFalse($config->verifySsl);
        $this->assertSame($config->toArray(), PHPWindConfig::fromArray($config->toArray())->toArray());
        $this->expectException(InvalidConfigurationException::class);
        PHPWindConfig::fromArray(['download_timeout' => '0']);
    }

    public function testShippedConfigReadsDownloadEnvironmentSettings(): void
    {
        $oldTimeout = getenv('PHPWIND_DOWNLOAD_TIMEOUT');
        $oldSsl = getenv('PHPWIND_VERIFY_SSL');
        putenv('PHPWIND_DOWNLOAD_TIMEOUT=37');
        putenv('PHPWIND_VERIFY_SSL=false');
        try {
            $values = require dirname(__DIR__) . '/config/phpwind.php';
            $config = PHPWindConfig::fromArray($values);
            $this->assertSame(37, $config->downloadTimeout);
            $this->assertFalse($config->verifySsl);
        } finally {
            $oldTimeout === false ? putenv('PHPWIND_DOWNLOAD_TIMEOUT') : putenv('PHPWIND_DOWNLOAD_TIMEOUT=' . $oldTimeout);
            $oldSsl === false ? putenv('PHPWIND_VERIFY_SSL') : putenv('PHPWIND_VERIFY_SSL=' . $oldSsl);
        }
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

    public function testValidateRejectsVersionWithTrailingGarbage(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        (new PHPWindConfig(version: 'v4.0.0oops'))->validate();
    }
}
