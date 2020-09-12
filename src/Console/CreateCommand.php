<?php

namespace OwenVoke\Torrent\Console;

use OwenVoke\Torrent\Torrent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('create')
            ->setDescription('Create a new .torrent file from a specified file')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The directory or file to create a .torrent for'
            )
            ->addOption(
                'piece-length',
                'p',
                InputOption::VALUE_REQUIRED,
                'Must be a power of two',
                65536
            )
            ->addOption(
                'v2-only',
                '2',
                InputOption::VALUE_NONE,
                'Don\'t generate v1 compatibility keys'
            )
            ->addOption(
                'tracker',
                't',
                InputOption::VALUE_REQUIRED,
                'The main announce tracker',
                'http://example.com/announce'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument('path');

        if (is_dir($path) || file_exists($path)) {
            $torrent = new Torrent($input->getOption('piece-length'));

            $torrent->prepare($path);

            $torrent->create(
                $input->getOption('tracker'),
                ! $input->getOption('v2-only')
            );

            $torrent->save();

            if (! $input->getOption('v2-only')) {
                $output->writeln('v1 Info Hash: '.$torrent->infoHashV1());
            }

            $output->writeln('v2 Info Hash: '.$torrent->infoHashV2());

            return;
        }

        $output->writeln('<fg=red>ERROR: Invalid input provided.</>');
    }
}
