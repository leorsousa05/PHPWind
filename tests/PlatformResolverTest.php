<?php

declare(strict_types=1);

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

    public function testSupportedPlatformMappingsAreExplicit(): void
    {
        self::assertSame('tailwindcss-linux-x64', PlatformResolver::getBinaryName('Linux', 'x86_64'));
        self::assertSame('tailwindcss-linux-arm64', PlatformResolver::getBinaryName('Linux', 'aarch64'));
        self::assertSame('tailwindcss-macos-x64', PlatformResolver::getBinaryName('Darwin', 'x86_64'));
        self::assertSame('tailwindcss-macos-arm64', PlatformResolver::getBinaryName('Darwin', 'arm64'));
        self::assertSame('tailwindcss-windows-x64.exe', PlatformResolver::getBinaryName('Windows', 'AMD64'));
        self::assertSame('tailwindcss-windows-arm64.exe', PlatformResolver::getBinaryName('Windows', 'aarch64'));
    }

    public function testUnsupportedPlatformIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlatformResolver::getBinaryName('FreeBSD', 'x86_64');
    }

    public function testUnsupportedArchitectureIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlatformResolver::getBinaryName('Linux', 'mips64');
    }

    public function testSemanticVersionRejectsNumericPrereleaseLeadingZeros(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlatformResolver::getDownloadUrl('v4.0.0-01');
    }

    public function testGetDownloadUrlFormatsV4Correctly(): void
    {
        $url = PlatformResolver::getDownloadUrl('v4.0.0');
        $this->assertStringStartsWith('https://github.com/tailwindcss/tailwindcss/releases/download/v4.0.0/', $url);
    }

    public function testGetDownloadUrlFormatsV3Correctly(): void
    {
        $url = PlatformResolver::getDownloadUrl('v3.4.17');
        $this->assertStringStartsWith('https://github.com/tailwindcss/tailwindcss-cli/releases/download/v3.4.17/', $url);
    }

    public function testGetVersionedBinaryNameContainsVersion(): void
    {
        $binary = PlatformResolver::getVersionedBinaryName('v4.0.0');
        $this->assertStringContainsString('tailwind-v4.0.0', $binary);
    }

    public function testGetVersionedBinaryNameHandlesVersionWithoutPrefix(): void
    {
        $binary = PlatformResolver::getVersionedBinaryName('3.4.17');
        $this->assertStringContainsString('tailwind-v3.4.17', $binary);
    }

    public function testGetVersionedBinaryNameUsesExeExtensionOnWindows(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('Windows-only test.');
        }

        $binary = PlatformResolver::getVersionedBinaryName('v4.0.0');
        $this->assertStringEndsWith('.exe', $binary);
    }

    public function testGetVersionedBinaryNameUsesNoExtensionOnUnix(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Unix-only test.');
        }

        $binary = PlatformResolver::getVersionedBinaryName('v4.0.0');
        $this->assertStringEndsNotWith('.exe', $binary);
    }
}
