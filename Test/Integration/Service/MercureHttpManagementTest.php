<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Service\MercureRefreshToken;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure token refresh service.
 */
#[DbIsolation(true)]
class MercureHttpManagementTest extends TestCase
{
    /**
     * Verify refresh returns a valid three-part JWT string.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/jwt_subscriber/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/jwt_subscriber/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testRefreshReturnsValidJwt(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $refreshToken = $objectManager->create(MercureRefreshToken::class);

        $jwt = $refreshToken->refresh();

        $this->assertNotEmpty($jwt);
        $this->assertCount(3, explode('.', $jwt), 'JWT must have three parts (header.payload.signature)');
    }

    /**
     * Verify refresh returns JWT with exp claim when TTL is configured.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/jwt_subscriber/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/jwt_subscriber/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    #[Config('mercure/jwt_subscriber/jwt_ttl', '120', 'store', 'default')]
    public function testRefreshReturnsJwtWithExpClaim(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $refreshToken = $objectManager->create(MercureRefreshToken::class);

        $jwt = $refreshToken->refresh();
        $parts = explode('.', $jwt);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('exp', $payload, 'JWT must contain exp claim when TTL is set');
        $this->assertGreaterThan(time(), $payload['exp'], 'JWT exp must be in the future');
    }
}
