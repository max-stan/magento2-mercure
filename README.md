# 📦 Magento 2 Mercure

> Magento 2 module that integrates [Symfony Mercure](https://github.com/symfony/mercure)
to enable real-time, server-sent event (SSE) publishing capabilities for your e-commerce store.

[![Packagist](https://img.shields.io/packagist/v/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Packagist](https://img.shields.io/packagist/dt/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Packagist](https://img.shields.io/packagist/dm/max-stan/magento2-mercure?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-mercure)
[![Tests](https://img.shields.io/github/actions/workflow/status/max-stan/magento2-mercure/main.yml?branch=master&style=for-the-badge&label=tests)](https://github.com/MaxStan/magento2-mercure/actions/workflows/main.yml)

This module provides a Mercure Hub integration layer for Magento 2, enabling real-time server-sent events (SSE) across
your storefront and admin panel. It handles JWT-based authentication for both publishing and subscribing, with separate
secrets for each operation. Topics are managed through an extensible resolver system that supports both public
(guest-accessible) and private (customer-specific) topic authorization.

> [!WARNING]
> This package is currently in **alpha** (`0.1.0-alpha1`). The API is unstable and may introduce breaking changes between releases. Do not use in production.

## ⭐ Features
- **SharedWorker SSE** — Single connection shared across browser tabs with automatic reconnection
- **JWT Authentication** — Separate publisher/subscriber token generation with configurable secrets, algorithms (HMAC SHA-256/384/512), and TTL
- **Topic-based authorization** — Third-party modules have an ability to register their own topics. 
Public and private topic separation with user-scoped access control

## 🛠️ Installation
To install Mercure in your Magento 2 project, follow these steps:

```shell
# Standard Magento module installation commands
composer config minimum-stability "alpha"
composer require max-stan/magento2-mercure:0.1.0-alpha1
bin/magento mod:en MaxStan_Mercure
bin/magento setup:upgrade
bin/magento setup:di:compile
# Sets config values
bin/magento config:set mercure/jwt_publisher/jwt_publisher_secret '!ChangeThisMercureHubJWTSecretKey!'
bin/magento config:set mercure/jwt_subscriber/jwt_subscriber_secret '!ChangeThisMercureHubJWTSecretKey!'
bin/magento config:set mercure/general/enabled 1
bin/magento c:f
# Install and configure Mercure Hub in dev mode, available via http://localhost:8080
docker run \
    -e SERVER_NAME=':80' \
    -e MERCURE_PUBLISHER_JWT_KEY='!ChangeThisMercureHubJWTSecretKey!' \
    -e MERCURE_SUBSCRIBER_JWT_KEY='!ChangeThisMercureHubJWTSecretKey!' \
    -p 8080:80 \
    dunglas/mercure caddy run --config /etc/caddy/dev.Caddyfile
```

## 🔁 Magento Compatibility
Can be installed on most 2.x Magento versions

## 🚀 Contributing
Contributions are welcome! If you find a bug or have a feature request, feel free to open an issue or submit a pull request.
