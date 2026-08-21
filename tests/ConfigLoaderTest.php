<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Config\ConfigLoader;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Exception\InvalidConfigurationException;
use PHPWind\Tests\Concerns\RemovesTempDirectories;

class ConfigLoaderTest extends TestCase
{
    use RemovesTempDirectories;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpwind_config_loader_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testFromArrayBuildsConfig(): void
    {
        $config = ConfigLoader::fromArray([
            'input_css' => 'resources/css/in.css',
            'output_css' => 'public/css/out.css',
            'version' => 'v3.4.17',
            'minify' => true,
        ]);

        $this->assertInstanceOf(PHPWindConfig::class, $config);
        $this->assertSame('resources/css/in.css', $config->inputCss);
        $this->assertSame('public/css/out.css', $config->outputCss);
        $this->assertSame('v3.4.17', $config->version);
        $this->assertTrue($config->minify);
    }

    public function testLoadReadsConfigFile(): void
    {
        $file = $this->tempDir . DIRECTORY_SEPARATOR . 'phpwind.php';
        file_put_contents($file, "<?php\nreturn ['input_css' => 'a.css', 'output_css' => 'b.css'];\n");

        $config = ConfigLoader::load($file);

        $this->assertSame('a.css', $config->inputCss);
        $this->assertSame('b.css', $config->outputCss);
    }

    public function testLoadThrowsWhenFileMissing(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        ConfigLoader::load($this->tempDir . DIRECTORY_SEPARATOR . 'missing.php');
    }

    public function testLoadThrowsWhenFileDoesNotReturnArray(): void
    {
        $file = $this->tempDir . DIRECTORY_SEPARATOR . 'phpwind.php';
        file_put_contents($file, "<?php\nreturn 'not-an-array';\n");

        $this->expectException(InvalidConfigurationException::class);

        ConfigLoader::load($file);
    }

    public function testLoadValidatesConfig(): void
    {
        $file = $this->tempDir . DIRECTORY_SEPARATOR . 'phpwind.php';
        file_put_contents($file, "<?php\nreturn ['version' => 'not-a-version'];\n");

        $this->expectException(InvalidConfigurationException::class);

        ConfigLoader::load($file);
    }

    public function testToArrayRoundTripsThroughFromArray(): void
    {
        $original = new PHPWindConfig(
            inputCss: 'x.css',
            outputCss: 'y.css',
            binaryDir: 'bin',
            version: 'v3.4.17',
            minify: true,
            watch: true
        );

        $roundTripped = ConfigLoader::fromArray($original->toArray());

        $this->assertSame($original->toArray(), $roundTripped->toArray());
    }
}
