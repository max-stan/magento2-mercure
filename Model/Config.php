<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

readonly class Config
{
    private const string MERCURE_HUB_SETTINGS_HUB_URL = 'mercure/hub_settings/hub_url';
    private const string MERCURE_HUB_SETTINGS_PUBLISHER_JWT_SECRET = 'mercure/hub_settings/publisher_jwt_secret';
    private const string MERCURE_HUB_SETTINGS_SUBSCRIBER_JWT_SECRET = 'mercure/hub_settings/subscriber_jwt_secret';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private EncryptorInterface $encryptor
    ) {
    }

    public function getHubUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::MERCURE_HUB_SETTINGS_HUB_URL
        );
    }

    public function getPublisherJwtSecret(): string
    {
        return $this->encryptor->decrypt(
            (string)$this->scopeConfig->getValue(self::MERCURE_HUB_SETTINGS_PUBLISHER_JWT_SECRET)
        );
    }

    public function getSubscriberJwtSecret(): string
    {
        return $this->encryptor->decrypt(
            (string)$this->scopeConfig->getValue(self::MERCURE_HUB_SETTINGS_SUBSCRIBER_JWT_SECRET)
        );
    }
}
