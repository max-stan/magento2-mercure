<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Service\MercureTopicResolver;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPrivateTopicProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPublicTopicProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure topic aggregation from providers.
 */
#[DbIsolation(true)]
class MercureTopicResolverTest extends TestCase
{
    /**
     * Verify empty array returned when no providers registered.
     */
    public function testGetAllowedPublicTopicsReturnsEmptyWhenNoProviders(): void
    {
        $resolver = Bootstrap::getObjectManager()->create(MercureTopicResolver::class, [
            'providers' => [],
        ]);

        $this->assertSame([], $resolver->getAllowedPublicTopics());
    }

    /**
     * Verify public topics are aggregated from all providers.
     */
    public function testGetAllowedPublicTopicsAggregatesFromProviders(): void
    {
        $resolver = Bootstrap::getObjectManager()->create(MercureTopicResolver::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $this->assertSame(
            ['https://example.com/public/notifications'],
            $resolver->getAllowedPublicTopics()
        );
    }

    /**
     * Verify private topics are aggregated with correct userId.
     */
    public function testGetAllowedPrivateTopicsAggregatesFromProviders(): void
    {
        $resolver = Bootstrap::getObjectManager()->create(MercureTopicResolver::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $this->assertSame(
            ['https://example.com/private/user/42/messages'],
            $resolver->getAllowedPrivateTopics(42, UserContextInterface::USER_TYPE_CUSTOMER)
        );
    }

    /**
     * Verify getAllAllowedTopics combines public and private when userId provided.
     */
    public function testGetAllAllowedTopicsCombinesPublicAndPrivate(): void
    {
        $resolver = Bootstrap::getObjectManager()->create(MercureTopicResolver::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $topics = $resolver->getAllAllowedTopics(42, UserContextInterface::USER_TYPE_CUSTOMER);

        $this->assertContains('https://example.com/public/notifications', $topics);
        $this->assertContains('https://example.com/private/user/42/messages', $topics);
        $this->assertCount(2, $topics);
    }

    /**
     * Verify getAllAllowedTopics returns only public topics when userId is null.
     */
    public function testGetAllAllowedTopicsReturnsOnlyPublicWhenNoUserId(): void
    {
        $resolver = Bootstrap::getObjectManager()->create(MercureTopicResolver::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $topics = $resolver->getAllAllowedTopics(null, null);

        $this->assertContains('https://example.com/public/notifications', $topics);
        $this->assertCount(1, $topics);
    }
}
