<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model\Jwt;

use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Jwt\TokenProvider;
use MaxStan\Mercure\Service\MercureTopicResolver;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPrivateTopicProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPublicTopicProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure JWT token generation.
 */
#[DbIsolation(true)]
class TokenProviderTest extends TestCase
{
    /**
     * Verify JWT has three dot-separated segments.
     */
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtReturnsValidJwtStructure(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(TokenProvider::class);
        $jwt = $tokenProvider->getJwt();

        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'JWT must have 3 dot-separated segments');
    }

    /**
     * Verify decoded JWT contains mercure.publish claim as array.
     */
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtContainsMercurePublishClaim(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(TokenProvider::class);
        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());

        $this->assertArrayHasKey('mercure', $payload);
        $this->assertArrayHasKey('publish', $payload['mercure']);
        $this->assertIsArray($payload['mercure']['publish']);
    }

    /**
     * Verify JWT contains future expiration when TTL is configured.
     */
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    #[Config('mercure/general/jwt_ttl', '3600', 'store', 'default')]
    public function testGetJwtContainsExpirationWhenTtlConfigured(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(TokenProvider::class);
        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());

        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    /**
     * Verify JWT publish claim contains topics from fixture providers.
     */
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtPublishClaimContainsResolvedTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $resolver = $objectManager->create(MercureTopicResolver::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ]
        ]);

        $mockUserContext = $this->createMock(UserContextInterface::class);
        $mockUserContext->method('getUserId')->willReturn(42);

        $tokenProvider = $objectManager->create(TokenProvider::class, [
            'mercureTopicProviderPool' => $resolver,
            'userContext' => $mockUserContext,
        ]);

        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());
        $topics = $payload['mercure']['publish'];

        $this->assertContains('https://example.com/public/notifications', $topics);
        $this->assertContains('https://example.com/private/user/42/messages', $topics);
    }

    /**
     * Decode the payload segment of a JWT string.
     */
    private function decodeJwtPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);

        return json_decode(
            base64_decode(strtr($parts[1], '-_', '+/')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
