# Magento 2 Mercure

> Magento 2 module that integrates [Symfony Mercure](https://github.com/symfony/mercure)
to enable real-time, server-sent event (SSE) publishing capabilities for your e-commerce store.

[![Packagist](https://img.shields.io/packagist/v/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Packagist](https://img.shields.io/packagist/dt/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Packagist](https://img.shields.io/packagist/dm/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Tests](https://img.shields.io/github/actions/workflow/status/max-stan/magento2-mercure/standards.yml?branch=master&style=for-the-badge&label=tests)](https://github.com/MaxStan/magento2-mercure/actions/workflows/standards.yml)

This module provides a Mercure Hub integration layer for Magento 2, enabling real-time server-sent events (SSE) across
your storefront and admin panel. It handles JWT-based authentication for both publishing and subscribing, with separate
secrets for each operation. Topics are managed through an extensible resolver system that supports both public 
(guest-accessible) and private (customer-specific) topic authorization.

## Installation
To install Mercure in your Magento 2 project, follow these steps:

```shell
composer require max-stan/magento2-mercure
bin/magento mod:en MaxStan_Mercure
bin/magento setup:upgrade
bin/magento setup:di:compile
```

## Magento Compatibility
Can be installed on most 2.x Magento versions

## Contributing
Contributions are welcome! If you find a bug or have a feature request, feel free to open an issue or submit a pull request.
