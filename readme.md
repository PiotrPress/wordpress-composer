# WordPress Composer

This composer plugin uses `WordPress.org API` to download WordPress core, plugins and themes.

## Installation

1. Add the plugin as a global composer requirement:

```shell
$ composer global require piotrpress/wordpress-composer
```

2. Allow the plugin execution:

```shell
$ composer config -g allow-plugins.piotrpress/wordpress-composer true
```

## Example `composer.json` file

```json
{
  "require": {
    "wordpress-core/full": "*",
    "wordpress-plugin/akismet": "*",
    "wordpress-theme/twentytwentytwo": "*",
    
    "piotrpress/wordpress-installer": "^1.0"
  },
  "config": {
    "allow-plugins": {
      "piotrpress/wordpress-installer": true
    }
  }
}
```

## Usage

- `wordpress-core/{$release}` - where `$release` is one of WordPress available release type: `full`, `no-content` or `new-bundled`
- `wordpress-theme/{$slug}` - where `$slug` is a desired WordPress theme slug
- `wordpress-plugin/{$slug}` - where `$slug` is a desired WordPress plugin slug

## Support

`WordPress Composer` sets `wordpress-core`, `wordpress-theme` and `wordpress-plugin` type, appropriately for the right composer packages, so it's compatible with all composer installers supporting this custom types, e.g.:
- [piotrpress/wordpress-installer](https://github.com/piotrpress/wordpress-installer)
- [composer/installers](https://github.com/composer/installers)
- [oomphinc/composer-installers-extender](https://github.com/oomphinc/composer-installers-extender)
- [johnpbloch/wordpress-core-installer](https://github.com/johnpbloch/wordpress-core-installer)
- [fancyguy/webroot-installer](https://github.com/fancyguy/webroot-installer)

## Resources

Check out example implementation in the [piotrpress/wordpress](https://github.com/PiotrPress/wordpress) package.

## Requirements

- PHP >= `7.4` version.
- Composer ^`2.0` version.

## License

[MIT](license.txt)