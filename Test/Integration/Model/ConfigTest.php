<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Config as MercureConfig;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure module configuration reader.
 */
#[DbIsolation(true)]
class ConfigTest extends TestCase
{
    private MercureConfig $config;

    protected function setUp(): void
    {
        $this->config = Bootstrap::getObjectManager()->get(MercureConfig::class);
    }

    /**
     * Verify module is disabled when no config is set.
     */
    public function testIsEnabledReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->config->isEnabled());
    }

    /**
     * Verify module reports enabled when configured.
     */
    #[Config('mercure/general/enabled', '1', 'store', 'default')]
    public function testIsEnabledReturnsTrueWhenConfigured(): void
    {
        $this->assertTrue($this->config->isEnabled());
    }

    /**
     * Verify hub URL is read from store-scoped config.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    public function testGetHubUrlReturnsConfiguredValue(): void
    {
        $this->assertSame('https://hub.test/.well-known/mercure', $this->config->getHubUrl());
    }

    /**
     * Verify JWT algorithm is read from store-scoped config.
     */
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha512', 'store', 'default')]
    public function testGetJwtAlgorithmReturnsConfiguredValue(): void
    {
        $this->assertSame('hmac.sha512', $this->config->getJwtAlgorithm());
    }

    /**
     * Verify JWT TTL is read as integer from store-scoped config.
     */
    #[Config('mercure/general/jwt_ttl', '7200', 'store', 'default')]
    public function testGetJwtTtlReturnsInteger(): void
    {
        $this->assertSame(7200, $this->config->getJwtTtl());
    }
}
