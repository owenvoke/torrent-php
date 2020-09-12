# Torrent

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-github-actions]][link-github-actions]
[![Style CI][ico-styleci]][link-styleci]
[![Total Downloads][ico-downloads]][link-downloads]
[![Buy us a tree][ico-treeware-gifting]][link-treeware-gifting]

A set of torrent management classes with support for BitTorrent v2.

## Install

Via Composer

```bash
$ composer require owenvoke/torrent
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
torrent create 'file_or_directory'
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@voke.dev instead of using the issue tracker.

## Credits

- [Owen Voke][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Treeware

You're free to use this package, but if it makes it to your production environment you are required to buy the world a tree.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to plant trees. If you support this package and contribute to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees [here][link-treeware-gifting].

Read more about Treeware at [treeware.earth][link-treeware].

[ico-version]: https://img.shields.io/packagist/v/owenvoke/skeleton-php.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-github-actions]: https://img.shields.io/github/workflow/status/owenvoke/skeleton-php/Tests.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/:styleci/shield
[ico-downloads]: https://img.shields.io/packagist/dt/owenvoke/skeleton-php.svg?style=flat-square
[ico-treeware-gifting]: https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen?style=flat-square

[link-packagist]: https://packagist.org/packages/owenvoke/skeleton-php
[link-github-actions]: https://github.com/owenvoke/skeleton-php/actions
[link-styleci]: https://styleci.io/repos/:styleci
[link-downloads]: https://packagist.org/packages/owenvoke/skeleton-php
[link-treeware]: https://treeware.earth
[link-treeware-gifting]: https://ecologi.com/owenvoke?gift-trees
[link-author]: https://github.com/owenvoke
[link-contributors]: ../../contributors

[BEP52]: https://github.com/bittorrent/bittorrent.org/blob/master/beps/bep_0052.rst
