<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model\Jwt;

use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Jwt\SubscriberTokenProvider;
use MaxStan\Mercure\Service\MercureSubscribeTopicsProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPrivateTopicProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPublicTopicProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure subscriber JWT token generation.
 */
#[DbIsolation(true)]
class SubscriberTokenProviderTest extends TestCase
{
    /**
     * Verify JWT has three dot-separated segments.
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtReturnsValidJwtStructure(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(SubscriberTokenProvider::class);
        $jwt = $tokenProvider->getJwt();

        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'JWT must have 3 dot-separated segments');
    }

    /**
     * Verify decoded JWT contains mercure.subscribe claim as array.
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtContainsMercureSubscribeClaim(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(SubscriberTokenProvider::class);
        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());

        $this->assertArrayHasKey('mercure', $payload);
        $this->assertArrayHasKey('subscribe', $payload['mercure']);
        $this->assertIsArray($payload['mercure']['subscribe']);
    }

    /**
     * Verify JWT publish claim is empty (subscriber token has no publish permissions).
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtHasEmptyPublishClaim(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(SubscriberTokenProvider::class);
        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());

        $this->assertEmpty($payload['mercure']['publish'] ?? []);
    }

    /**
     * Verify JWT contains future expiration when TTL is configured.
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    #[Config('mercure/general/jwt_ttl', '3600', 'store', 'default')]
    public function testGetJwtContainsExpirationWhenTtlConfigured(): void
    {
        $tokenProvider = Bootstrap::getObjectManager()->create(SubscriberTokenProvider::class);
        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());

        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    /**
     * Verify JWT subscribe claim contains topics from fixture providers.
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtSubscribeClaimContainsResolvedTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $resolver = $objectManager->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ]
        ]);

        $mockUserContext = $this->createMock(UserContextInterface::class);
        $mockUserContext->method('getUserId')->willReturn(42);
        $mockUserContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $tokenProvider = $objectManager->create(SubscriberTokenProvider::class, [
            'mercureTopicProviderPool' => $resolver,
            'userContext' => $mockUserContext,
        ]);

        $payload = $this->decodeJwtPayload($tokenProvider->getJwt());
        $topics = $payload['mercure']['subscribe'];

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
