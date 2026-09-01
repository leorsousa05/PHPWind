<?php

declare(strict_types=1);

namespace PHPWind\Symfony\Command;

use PHPWind\Command\InitHandler;
use PHPWind\Config\PHPWindConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'phpwind:init', description: 'Initialize Tailwind CSS input file for Symfony')]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, 'Input CSS path', 'assets/styles/app.css')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output CSS path', 'public/css/app.css');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputPath = (string) $input->getOption('input');
        $outputPath = (string) $input->getOption('output');

        $config = new PHPWindConfig(inputCss: $inputPath, outputCss: $outputPath);
        $handler = new InitHandler();
        $handler->handle($config);

        $io->success("PHPWind initialized input CSS at {$inputPath}");

        return 0;
    }
}
