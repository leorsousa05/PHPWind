<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Helper\AssetHelper;
use PHPWind\Manifest\AssetEntry;
use PHPWind\Manifest\AssetManifest;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class AssetHelperTest extends TestCase
{
    use RemovesTempDirectories;
    private string $originalCwd;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->originalCwd = getcwd() ?: '.';
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_asset_helper_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css', 0755, true);
        chdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        chdir($this->originalCwd);
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testCssReturnsQueryStringTagWhenFileExists(): void
    {
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'public/css/app.css', 'body{}');
        $expectedHash = substr(md5_file($this->tempDir . DIRECTORY_SEPARATOR . 'public/css/app.css') ?: '', 0, 8);

        $tag = AssetHelper::css('css/app.css');

        $this->assertStringContainsString('<link rel="stylesheet" href="/css/app.css?v=' . $expectedHash . '">', $tag);
    }

    public function testCssReturnsUnversionedTagWhenFileMissing(): void
    {
        $tag = AssetHelper::css('css/app.css');

        $this->assertSame('<link rel="stylesheet" href="/css/app.css">', $tag);
    }

    public function testCssReturnsUnversionedTagWhenVersionedIsFalse(): void
    {
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'public/css/app.css', 'body{}');

        $tag = AssetHelper::css('css/app.css', false);

        $this->assertSame('<link rel="stylesheet" href="/css/app.css">', $tag);
    }

    public function testCssEscapesSpecialCharacters(): void
    {
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'public/css/app.css', 'body{}');

        $tag = AssetHelper::css('css/app\'.css');

        $this->assertStringNotContainsString("'", $tag);
        $this->assertStringContainsString('&#039;', $tag);
    }

    public function testCssUsesCustomPublicDir(): void
    {
        $customDir = $this->tempDir . DIRECTORY_SEPARATOR . 'dist';
        mkdir($customDir . DIRECTORY_SEPARATOR . 'css', 0755, true);
        file_put_contents($customDir . DIRECTORY_SEPARATOR . 'css/app.css', 'body{}');
        $expectedHash = substr(md5_file($customDir . DIRECTORY_SEPARATOR . 'css/app.css') ?: '', 0, 8);

        $tag = AssetHelper::css('css/app.css', true, $customDir);

        $this->assertStringContainsString('/css/app.css?v=' . $expectedHash, $tag);
    }

    public function testCssFromManifestReturnsVersionedUrl(): void
    {
        $manifest = new AssetManifest([
            'css/app.css' => new AssetEntry(path: 'css/app.css', hash: 'a1b2c3d4'),
        ]);

        $tag = AssetHelper::cssFromManifest($manifest, 'css/app.css');

        $this->assertSame('<link rel="stylesheet" href="/css/app.css?v=a1b2c3d4">', $tag);
    }

    public function testCssFromManifestReturnsUnversionedWhenEntryMissing(): void
    {
        $manifest = new AssetManifest();

        $tag = AssetHelper::cssFromManifest($manifest, 'css/app.css');

        $this->assertSame('<link rel="stylesheet" href="/css/app.css">', $tag);
    }

    public function testCssFromManifestReturnsUnversionedWhenVersionedIsFalse(): void
    {
        $manifest = new AssetManifest([
            'css/app.css' => new AssetEntry(path: 'css/app.css', hash: 'a1b2c3d4'),
        ]);

        $tag = AssetHelper::cssFromManifest($manifest, 'css/app.css', false);

        $this->assertSame('<link rel="stylesheet" href="/css/app.css">', $tag);
    }
}
