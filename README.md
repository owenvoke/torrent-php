# torrent-v2

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Style CI][ico-styleci]][link-styleci]
[![Code Coverage][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A set of torrent management classes with support for Torrent v2.

## Install

Via Composer

```bash
$ composer require pxgamer/torrent-v2
```

## Usage

See [BEP52] for more information on the BitTorrent v2 proposal.

_Please note, this is still a work-in-progress, and is by no means usable at the moment._

In future, this will be a standalone library, the CLI will be distributed separately.

**List available commands**

```bash
torrent
```

**Create a torrent file for a file or directory**

```bash
torrent create [file_or_directory]
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@pxgamer.xyz instead of using the issue tracker.

## Credits

- [pxgamer][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/pxgamer/torrent-v2.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/pxgamer/torrent-v2/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/104362826/shield
[ico-code-quality]: https://img.shields.io/codecov/c/github/pxgamer/torrent-v2.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/pxgamer/torrent-v2.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/pxgamer/torrent-v2
[link-travis]: https://travis-ci.org/pxgamer/torrent-v2
[link-styleci]: https://styleci.io/repos/104362826
[link-code-quality]: https://codecov.io/gh/pxgamer/torrent-v2
[link-downloads]: https://packagist.org/packages/pxgamer/torrent-v2
[link-author]: https://github.com/pxgamer
[link-contributors]: ../../contributors
[BEP52]: https://github.com/bittorrent/bittorrent.org/blob/master/beps/bep_0052.rst
