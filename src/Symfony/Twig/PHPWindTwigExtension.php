<?php

namespace PHPWind\Symfony\Twig;

use PHPWind\Helper\AssetHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PHPWindTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('phpwind_css', [$this, 'renderCss'], ['is_safe' => ['html']]),
        ];
    }

    public function renderCss(string $path = 'css/app.css', bool $versioned = true): string
    {
        return AssetHelper::css($path, $versioned);
    }
}
