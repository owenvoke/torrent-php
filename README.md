# torrent-v2

A set of torrent management classes with support for Torrent v2.

See [BEP52] for more information on the BitTorrent v2 proposal.

_Please note, this is still a work-in-progress, and is by no means usable at the moment._

In future, this will be a standalone class package, the CLI will be distributed separately.

## Development

1. Clone the repository using `git clone https://github.com/pxgamer/torrent-v2`
2. Run `composer install` in the directory

## Usage

__`torrent`__ - List available commands  
__`torrent create {file/directory}`__ - Create a torrent file for a file or directory  
__`torrent create --help`__ - List arguments and options for the `torrent create` command  

[BEP52]: https://github.com/bittorrent/bittorrent.org/blob/master/beps/bep_0052.rst