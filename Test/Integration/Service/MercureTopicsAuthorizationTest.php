<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\MercureTopicsAuthorizationInterface;
use MaxStan\Mercure\Api\PublicTopicsResolverInterface;
use MaxStan\Mercure\Api\TopicsResolverInterface;
use MaxStan\Mercure\Model\MercureTopicsProvider;
use MaxStan\Mercure\Service\MercureTopicsAuthorization;
use PHPUnit\Framework\TestCase;

class MercureTopicsAuthorizationTest extends TestCase
{
    private function createPublicTopicResolver(): TopicsResolverInterface&PublicTopicsResolverInterface
    {
        return new class implements TopicsResolverInterface, PublicTopicsResolverInterface {
            public function getTopics(?int $customerId): array
            {
                return ['https://example.com/public-topic'];
            }
        };
    }

    private function createPrivateTopicResolver(): TopicsResolverInterface
    {
        return new class implements TopicsResolverInterface {
            public function getTopics(?int $customerId): array
            {
                return ['https://example.com/private-topic'];
            }
        };
    }

    private function createAuthorizationWithResolvers(array $resolvers): MercureTopicsAuthorizationInterface
    {
        $objectManager = Bootstrap::getObjectManager();

        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            [
                'publishTopicResolvers' => $resolvers
            ]
        );

        return $objectManager->create(
            MercureTopicsAuthorization::class,
            [
                'publishTopicProvidersPool' => $provider
            ]
        );
    }

    public function testGetAllowedTopicsWithNullCustomerReturnsOnlyPublic(): void
    {
        $authorization = $this->createAuthorizationWithResolvers([
            $this->createPublicTopicResolver(),
            $this->createPrivateTopicResolver()
        ]);

        $topics = $authorization->getAllowedTopics(null);

        $this->assertCount(1, $topics);
        $this->assertEquals(['https://example.com/public-topic'], $topics);
    }

    public function testGetAllowedTopicsWithCustomerReturnsBothPublicAndPrivate(): void
    {
        $authorization = $this->createAuthorizationWithResolvers([
            $this->createPublicTopicResolver(),
            $this->createPrivateTopicResolver()
        ]);

        $topics = $authorization->getAllowedTopics(123);

        $this->assertCount(2, $topics);
        $this->assertContains('https://example.com/public-topic', $topics);
        $this->assertContains('https://example.com/private-topic', $topics);
    }

    public function testGetAllowedTopicsWithZeroCustomerReturnsOnlyPublic(): void
    {
        $authorization = $this->createAuthorizationWithResolvers([
            $this->createPublicTopicResolver(),
            $this->createPrivateTopicResolver()
        ]);

        $topics = $authorization->getAllowedTopics(0);

        $this->assertCount(1, $topics);
        $this->assertEquals(['https://example.com/public-topic'], $topics);
    }

    public function testGetAllowedTopicsWithNoResolversReturnsEmpty(): void
    {
        $authorization = $this->createAuthorizationWithResolvers([]);

        $topics = $authorization->getAllowedTopics(null);

        $this->assertEmpty($topics);
        $this->assertEquals([], $topics);
    }
}
