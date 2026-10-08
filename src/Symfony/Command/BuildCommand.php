<?php

declare(strict_types=1);

namespace PHPWind\Symfony\Command;

use PHPWind\Binary\PlatformResolver;
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use PHPWind\Config\Env;
use PHPWind\Command\BuildOutputFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'phpwind:build', description: 'Build Tailwind CSS using standalone CLI for Symfony')]
class BuildCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, 'Input CSS path', 'assets/styles/app.css')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output CSS path', 'public/css/app.css')
            ->addOption('minify', 'm', InputOption::VALUE_NONE, 'Minify CSS output')
            ->addOption('tailwind-version', null, InputOption::VALUE_OPTIONAL, 'Tailwind version', PlatformResolver::DEFAULT_VERSION);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('PHPWind CSS Build');

        $config = new PHPWindConfig(
            inputCss: (string) $input->getOption('input'),
            outputCss: (string) $input->getOption('output'),
            version: (string) $input->getOption('tailwind-version'),
            minify: (bool) $input->getOption('minify'),
            downloadTimeout: (int) Env::get('PHPWIND_DOWNLOAD_TIMEOUT', 120),
            verifySsl: filter_var(Env::get('PHPWIND_VERIFY_SSL', true), FILTER_VALIDATE_BOOL)
        );

        $compiler = new TailwindCompiler();
        try { $result = $compiler->compileResult($config); }
        catch (\Throwable $exception) {
            $io->error(BuildOutputFormatter::exception($exception));
            return 1;
        }
        $exitCode = $result->exitCode;

        if ($exitCode === 0) {
            $io->success(BuildOutputFormatter::success());
        } else {
            $io->error(BuildOutputFormatter::failure($result));
        }

        return $exitCode;
    }
}
