<?php

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Symfony\PHPWindBundle;
use PHPWind\Symfony\Twig\PHPWindTwigExtension;
use PHPWind\Symfony\Command\BuildCommand;
use Symfony\Component\Console\Tester\CommandTester;

class SymfonyTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new PHPWindBundle();
        $this->assertInstanceOf(PHPWindBundle::class, $bundle);
    }

    public function testTwigExtensionRegistersFunction(): void
    {
        $extension = new PHPWindTwigExtension();
        $functions = $extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertEquals('phpwind_css', $functions[0]->getName());
    }

    public function testTwigExtensionRenderCssOutput(): void
    {
        $extension = new PHPWindTwigExtension();
        $rendered = $extension->renderCss('css/app.css', false);

        $this->assertStringContainsString('<link rel="stylesheet" href="/css/app.css">', $rendered);
    }

    public function testBuildCommandUsesSharedFailureWordingAndExitCode(): void
    {
        $tester = new CommandTester(new BuildCommand());
        $code = $tester->execute(['--input' => '/definitely/missing/phpwind-input.css']);
        $this->assertSame(1, $code);
        $this->assertStringContainsString('Tailwind CSS build failed: Input CSS file does not exist', $tester->getDisplay());
    }
}
