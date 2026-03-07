<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration reader for Mercure module.
 */
readonly class Config
{
    private const string XML_PATH_ENABLED = 'mercure/general/enabled';
    private const string XML_PATH_HUB_URL = 'mercure/general/hub_url';
    private const string XML_PATH_JWT_PUBLISHER_SECRET = 'mercure/jwt_publisher/jwt_publisher_secret';
    private const string XML_PATH_JWT_SUBSCRIBER_SECRET = 'mercure/jwt_subscriber/jwt_subscriber_secret';
    private const string XML_PATH_JWT_PUBLISHER_ALGORITHM = 'mercure/jwt_publisher/jwt_algorithm';
    private const string XML_PATH_JWT_SUBSCRIBER_ALGORITHM = 'mercure/jwt_subscriber/jwt_algorithm';
    private const string XML_PATH_JWT_PUBLISHER_TTL = 'mercure/jwt_publisher/jwt_ttl';
    private const string XML_PATH_JWT_SUBSCRIBER_TTL = 'mercure/jwt_subscriber/jwt_ttl';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getHubUrl(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_HUB_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getJwtPublisherSecret(?int $storeId = null): ?string
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_PUBLISHER_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$value) {
            return null;
        }

        return $this->encryptor->decrypt($value);
    }

    public function getJwtSubscriberSecret(?int $storeId = null): ?string
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_SUBSCRIBER_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$value) {
            return null;
        }

        return $this->encryptor->decrypt($value);
    }

    public function getJwtPublisherAlgorithm(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_PUBLISHER_ALGORITHM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getJwtSubscriberAlgorithm(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_SUBSCRIBER_ALGORITHM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get JWT token lifetime in seconds.
     */
    public function getJwtPublisherTtl(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_PUBLISHER_TTL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getJwtSubscriberTtl(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_SUBSCRIBER_TTL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
