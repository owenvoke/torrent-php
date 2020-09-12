<?php

namespace OwenVoke\Torrent\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

final class Application extends BaseApplication
{
    public const NAME = 'BitTorrent v2 Manager';
    public const VERSION = '@git-version@';

    /**
     * Application constructor.
     *
     * @param null|string $name
     * @param null|string $version
     */
    public function __construct(?string $name = null, ?string $version = null)
    {
        if (! $version) {
            $version = static::VERSION === '@'.'git-version@' ?
                'source' :
                static::VERSION;
        }

        parent::__construct(
            $name ?: static::NAME,
            $version
        );
    }

    /** @return Command[] */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new CreateCommand();
        $commands[] = new InfoCommand();

        return $commands;
    }
}
