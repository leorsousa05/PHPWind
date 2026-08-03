<?php

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Config\PHPWindConfig;

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
}
