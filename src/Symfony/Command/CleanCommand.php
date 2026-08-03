<?php

namespace PHPWind\Symfony\Command;

use PHPWind\Command\CleanHandler;
use PHPWind\Config\PHPWindConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'phpwind:clean', description: 'Clean downloaded Tailwind CLI binary and assets for Symfony')]
class CleanCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Also remove compiled output CSS file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = new PHPWindConfig();
        $handler = new CleanHandler();
        $handler->handle($config, (bool) $input->getOption('all'));

        $io->success('PHPWind cache and binary cleaned successfully.');

        return 0;
    }
}
