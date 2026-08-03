<?php

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Binary\PlatformResolver;

class PlatformResolverTest extends TestCase
{
    public function testGetBinaryNameIsNotEmpty(): void
    {
        $binary = PlatformResolver::getBinaryName();
        $this->assertNotEmpty($binary);
        $this->assertStringContainsString('tailwindcss', $binary);
    }

    public function testGetDownloadUrlFormatsCorrectly(): void
    {
        $url = PlatformResolver::getDownloadUrl('v4.0.0');
        $this->assertStringStartsWith('https://github.com/tailwindcss/tailwindcss/releases/download/v4.0.0/', $url);
    }
}
