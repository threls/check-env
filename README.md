# This is my package check-env

[![Latest Version on Packagist](https://img.shields.io/packagist/v/threls/check-env.svg?style=flat-square)](https://packagist.org/packages/threls/check-env)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/threls/check-env/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/threls/check-env/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/threls/check-env/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/threls/check-env/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/threls/check-env.svg?style=flat-square)](https://packagist.org/packages/threls/check-env)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/check-env.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/check-env)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

Steps to install the package

- Add it to composer.json
  
```php
 "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/threls/check-env"
        }
    ]
```

- Run composer require
  
```bash
composer require threls/check-env
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="check-env-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
 php artisan check-env
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [sabina](https://github.com/sabina1997)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
