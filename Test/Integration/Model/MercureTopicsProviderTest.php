<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\PublicTopicsResolverInterface;
use MaxStan\Mercure\Api\TopicsResolverInterface;
use MaxStan\Mercure\Model\MercureTopicsProvider;
use PHPUnit\Framework\TestCase;

class MercureTopicsProviderTest extends TestCase
{
    private function createPublicResolver(array $topics): TopicsResolverInterface
    {
        return new class($topics) implements TopicsResolverInterface, PublicTopicsResolverInterface {
            public function __construct(private readonly array $topics)
            {
            }

            public function getTopics(?int $customerId = null): array
            {
                return $this->topics;
            }
        };
    }

    private function createPrivateResolver(array $topics): TopicsResolverInterface
    {
        return new class($topics) implements TopicsResolverInterface {
            public function __construct(private readonly array $topics)
            {
            }

            public function getTopics(?int $customerId = null): array
            {
                return $this->topics;
            }
        };
    }

    public function testGetPublicTopicsReturnsOnlyPublicResolverTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $publicResolver = $this->createPublicResolver(['https://example.com/public1']);
        $privateResolver = $this->createPrivateResolver(['https://example.com/private1']);

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$publicResolver, $privateResolver]]
        );

        $result = $provider->getPublicTopics();

        $this->assertCount(1, $result);
        $this->assertEquals(['https://example.com/public1'], $result);
    }

    public function testGetPrivateTopicsReturnsOnlyPrivateResolverTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $publicResolver = $this->createPublicResolver(['https://example.com/public1']);
        $privateResolver = $this->createPrivateResolver(['https://example.com/private1']);

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$publicResolver, $privateResolver]]
        );

        $result = $provider->getPrivateTopics(123);

        $this->assertCount(1, $result);
        $this->assertEquals(['https://example.com/private1'], $result);
    }

    public function testGetPublicTopicsWithNoResolvers(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => []]
        );

        $result = $provider->getPublicTopics();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetPrivateTopicsWithNoResolvers(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => []]
        );

        $result = $provider->getPrivateTopics(123);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testMultiplePublicResolversMergeTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $publicResolver1 = $this->createPublicResolver(['https://example.com/public1']);
        $publicResolver2 = $this->createPublicResolver(['https://example.com/public2', 'https://example.com/public3']);

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$publicResolver1, $publicResolver2]]
        );

        $result = $provider->getPublicTopics();

        $this->assertCount(3, $result);
        $this->assertEquals([
            'https://example.com/public1',
            'https://example.com/public2',
            'https://example.com/public3'
        ], $result);
    }

    public function testMultiplePrivateResolversMergeTopics(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $privateResolver1 = $this->createPrivateResolver(['https://example.com/private1']);
        $privateResolver2 = $this->createPrivateResolver(['https://example.com/private2', 'https://example.com/private3']);

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$privateResolver1, $privateResolver2]]
        );

        $result = $provider->getPrivateTopics(123);

        $this->assertCount(3, $result);
        $this->assertEquals([
            'https://example.com/private1',
            'https://example.com/private2',
            'https://example.com/private3'
        ], $result);
    }

    public function testGetPublicTopicsPassesCustomerIdToResolvers(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $capturedCustomerId = null;
        $publicResolver = new class($capturedCustomerId) implements TopicsResolverInterface, PublicTopicsResolverInterface {
            public function __construct(private mixed &$capturedCustomerId)
            {
            }

            public function getTopics(?int $customerId = null): array
            {
                $this->capturedCustomerId = $customerId;
                return ['https://example.com/public'];
            }
        };

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$publicResolver]]
        );

        $provider->getPublicTopics(456);

        $this->assertEquals(456, $capturedCustomerId);
    }

    public function testGetPrivateTopicsPassesCustomerIdToResolvers(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $capturedCustomerId = null;
        $privateResolver = new class($capturedCustomerId) implements TopicsResolverInterface {
            public function __construct(private mixed &$capturedCustomerId)
            {
            }

            public function getTopics(?int $customerId = null): array
            {
                $this->capturedCustomerId = $customerId;
                return ['https://example.com/private'];
            }
        };

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [$privateResolver]]
        );

        $provider->getPrivateTopics(789);

        $this->assertEquals(789, $capturedCustomerId);
    }

    public function testMixedResolversAreFilteredCorrectly(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $publicResolver1 = $this->createPublicResolver(['https://example.com/public1']);
        $privateResolver1 = $this->createPrivateResolver(['https://example.com/private1']);
        $publicResolver2 = $this->createPublicResolver(['https://example.com/public2']);
        $privateResolver2 = $this->createPrivateResolver(['https://example.com/private2']);

        /** @var MercureTopicsProvider $provider */
        $provider = $objectManager->create(
            MercureTopicsProvider::class,
            ['publishTopicResolvers' => [
                $publicResolver1,
                $privateResolver1,
                $publicResolver2,
                $privateResolver2
            ]]
        );

        $publicTopics = $provider->getPublicTopics();
        $this->assertCount(2, $publicTopics);
        $this->assertEquals(['https://example.com/public1', 'https://example.com/public2'], $publicTopics);

        $privateTopics = $provider->getPrivateTopics(123);
        $this->assertCount(2, $privateTopics);
        $this->assertEquals(['https://example.com/private1', 'https://example.com/private2'], $privateTopics);
    }
}
