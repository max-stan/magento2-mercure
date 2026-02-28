<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration reader for Mercure module.
 */
readonly class Config
{
    private const string XML_PATH_ENABLED = 'mercure/general/enabled';
    private const string XML_PATH_HUB_URL = 'mercure/general/hub_url';
    private const string XML_PATH_JWT_SECRET = 'mercure/general/jwt_secret';
    private const string XML_PATH_JWT_ALGORITHM = 'mercure/general/jwt_algorithm';
    private const string XML_PATH_JWT_TTL = 'mercure/general/jwt_ttl';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
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

    public function getJwtSecret(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_JWT_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getJwtAlgorithm(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_ALGORITHM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get JWT token lifetime in seconds.
     */
    public function getJwtTtl(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_JWT_TTL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
