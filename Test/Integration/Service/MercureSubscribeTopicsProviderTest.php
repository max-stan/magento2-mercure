<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Service\MercureSubscribeTopicsProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPrivateTopicProvider;
use MaxStan\Mercure\Test\Integration\Fixtures\TestPublicTopicProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure subscribe topic aggregation from providers.
 */
#[DbIsolation(true)]
class MercureSubscribeTopicsProviderTest extends TestCase
{
    /**
     * Verify empty array returned when no providers registered.
     */
    public function testGetPublicTopicsReturnsEmptyWhenNoProviders(): void
    {
        $provider = Bootstrap::getObjectManager()->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [],
        ]);

        $this->assertSame([], $provider->getPublicTopics());
    }

    /**
     * Verify public topics are aggregated from all providers.
     */
    public function testGetPublicTopicsAggregatesFromProviders(): void
    {
        $provider = Bootstrap::getObjectManager()->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $this->assertSame(
            ['https://example.com/public/notifications'],
            $provider->getPublicTopics()
        );
    }

    /**
     * Verify private topics are aggregated with correct userId.
     */
    public function testGetPrivateTopicsAggregatesFromProviders(): void
    {
        $provider = Bootstrap::getObjectManager()->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $this->assertSame(
            ['https://example.com/private/user/42/messages'],
            $provider->getPrivateTopics(42, UserContextInterface::USER_TYPE_CUSTOMER)
        );
    }

    /**
     * Verify getAllTopics combines public and private when userId provided.
     */
    public function testGetAllTopicsCombinesPublicAndPrivate(): void
    {
        $provider = Bootstrap::getObjectManager()->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $topics = $provider->getAllTopics(42, UserContextInterface::USER_TYPE_CUSTOMER);

        $this->assertContains('https://example.com/public/notifications', $topics);
        $this->assertContains('https://example.com/private/user/42/messages', $topics);
        $this->assertCount(2, $topics);
    }

    /**
     * Verify getAllTopics returns only public topics when userId is null.
     */
    public function testGetAllTopicsReturnsOnlyPublicWhenNoUserId(): void
    {
        $provider = Bootstrap::getObjectManager()->create(MercureSubscribeTopicsProvider::class, [
            'providers' => [
                new TestPublicTopicProvider(),
                new TestPrivateTopicProvider(),
            ],
        ]);

        $topics = $provider->getAllTopics(null, null);

        $this->assertContains('https://example.com/public/notifications', $topics);
        $this->assertCount(1, $topics);
    }
}
