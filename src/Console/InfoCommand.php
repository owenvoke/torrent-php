<?php

namespace OwenVoke\Torrent\Console;

use Exception;
use OwenVoke\Torrent\Bencode;
use OwenVoke\Torrent\Exceptions\BencodeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InfoCommand extends Command
{
    /**
     * @var string
     */
    private const INFO = 'info';

    /**
     * @var string
     */
    private const PATH = 'path';

    /**
     * @var string
     */
    private const N_A = 'n/a';

    protected function configure(): void
    {
        $this
            ->setName(self::INFO)
            ->setDescription('View the details for a specific .torrent file')
            ->addArgument(
                self::PATH,
                InputArgument::REQUIRED,
                'The .torrent file to view details for'
            );
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument(self::PATH);

        if (is_dir($path) || file_exists($path)) {
            $torrentData = file_get_contents($input->getArgument(self::PATH));

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
     * @param  array  $data
     * @return array
     */
    private function formatDetails(array $data): array
    {
        return [
            'Name: '.$data[self::INFO]['name'] ?? self::N_A,
            'Meta version: '.$data[self::INFO]['meta version'] ?? 1,
            'Piece length: '.$data[self::INFO]['piece length'] ?? self::N_A,
            'Announce: '.$data['announce'] ?? self::N_A,
        ];
    }
}
