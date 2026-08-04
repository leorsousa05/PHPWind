<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Exception\AssetManifestException;
use PHPWind\Manifest\AssetEntry;
use PHPWind\Manifest\AssetManifest;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class AssetManifestTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_manifest_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testFromArrayAndToArrayRoundTrip(): void
    {
        $manifest = AssetManifest::fromArray([
            'css/app.css' => ['path' => 'css/app.css', 'hash' => 'a1b2c3d4'],
        ]);

        $this->assertSame([
            'css/app.css' => ['path' => 'css/app.css', 'hash' => 'a1b2c3d4'],
        ], $manifest->toArray());
    }

    public function testFromArraySupportsFlatSchema(): void
    {
        $manifest = AssetManifest::fromArray([
            'css/app.css' => 'a1b2c3d4',
        ]);

        $entry = $manifest->get('css/app.css');
        $this->assertInstanceOf(AssetEntry::class, $entry);
        $this->assertSame('css/app.css', $entry->path);
        $this->assertSame('a1b2c3d4', $entry->hash);
    }

    public function testReadAndWriteRoundTrip(): void
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = new AssetManifest([
            'css/app.css' => new AssetEntry(path: 'css/app.css', hash: 'a1b2c3d4'),
        ]);

        $manifest->write($path);
        $read = AssetManifest::read($path);

        $this->assertSame($manifest->toArray(), $read->toArray());
    }

    public function testReadThrowsWhenFileMissing(): void
    {
        $this->expectException(AssetManifestException::class);
        AssetManifest::read($this->tempDir . DIRECTORY_SEPARATOR . 'missing.json');
    }

    public function testReadThrowsWhenJsonInvalid(): void
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'manifest.json';
        file_put_contents($path, 'not-json');

        $this->expectException(AssetManifestException::class);
        AssetManifest::read($path);
    }

    public function testGetReturnsNullForMissingEntry(): void
    {
        $manifest = new AssetManifest();

        $this->assertNull($manifest->get('css/missing.css'));
    }

    public function testSetAddsEntry(): void
    {
        $manifest = new AssetManifest();
        $manifest->set('css/app.css', new AssetEntry(path: 'css/app.css', hash: 'a1b2c3d4'));

        $this->assertInstanceOf(AssetEntry::class, $manifest->get('css/app.css'));
    }

    public function testGenerateComputesHashesForExistingFiles(): void
    {
        $publicDir = $this->tempDir . DIRECTORY_SEPARATOR . 'public';
        mkdir($publicDir . DIRECTORY_SEPARATOR . 'css', 0755, true);
        file_put_contents($publicDir . DIRECTORY_SEPARATOR . 'css/app.css', 'body{}');

        $manifest = AssetManifest::generate($publicDir, ['css/app.css']);
        $entry = $manifest->get('css/app.css');

        $this->assertInstanceOf(AssetEntry::class, $entry);
        $this->assertSame('css/app.css', $entry->path);
        $this->assertSame(8, strlen($entry->hash));
        $this->assertSame(substr(md5_file($publicDir . DIRECTORY_SEPARATOR . 'css/app.css') ?: '', 0, 8), $entry->hash);
    }

    public function testGenerateLeavesEmptyHashForMissingFiles(): void
    {
        $manifest = AssetManifest::generate($this->tempDir, ['css/app.css']);
        $entry = $manifest->get('css/app.css');

        $this->assertInstanceOf(AssetEntry::class, $entry);
        $this->assertSame('', $entry->hash);
    }

    public function testEntryUrl(): void
    {
        $entry = new AssetEntry(path: 'css/app.css', hash: 'a1b2c3d4');

        $this->assertSame('/css/app.css?v=a1b2c3d4', $entry->url());
        $this->assertSame('/assets/css/app.css?v=a1b2c3d4', $entry->url('/assets'));
    }
}
