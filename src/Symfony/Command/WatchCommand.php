<?php

namespace PHPWind\Symfony\Command;

use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'phpwind:watch', description: 'Start Tailwind CSS watcher for Symfony')]
class WatchCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, 'Input CSS path', 'assets/styles/app.css')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output CSS path', 'public/css/app.css')
            ->addOption('tailwind-version', null, InputOption::VALUE_OPTIONAL, 'Tailwind version', 'v4.0.0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting PHPWind Symfony Watcher...');

        $config = new PHPWindConfig(
            inputCss: (string) $input->getOption('input'),
            outputCss: (string) $input->getOption('output'),
            version: (string) $input->getOption('tailwind-version'),
            watch: true
        );

        $compiler = new TailwindCompiler();
        return $compiler->compile($config);
    }
}
