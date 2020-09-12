<?php

namespace OwenVoke\Torrent\Console;

use OwenVoke\Torrent\Bencode;
use OwenVoke\Torrent\Exceptions\BencodeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InfoCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('info')
            ->setDescription('View the details for a specific .torrent file')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The .torrent file to view details for'
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
            $torrentData = file_get_contents($input->getArgument('path'));

            try {
                $decodedData = Bencode::decode($torrentData);
            } catch (BencodeException $exception) {
                $output->writeln('<fg=red>ERROR: '.$exception->getMessage().'</>');

                return;
            }

            if ($decodedData) {
                $output->writeln($this->formatDetails($decodedData));

                return;
            }
        }

        $output->writeln('<fg=red>ERROR: Invalid input provided.</>');
    }

    /**
     * @param array $data
     * @return array
     */
    private function formatDetails(array $data): array
    {
        return [
            'Name: '.$data['info']['name'] ?? 'n/a',
            'Meta version: '.$data['info']['meta version'] ?? 1,
            'Piece length: '.$data['info']['piece length'] ?? 'n/a',
            'Announce: '.$data['announce'] ?? 'n/a',
        ];
    }
}
