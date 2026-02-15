<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\PublicTopicsResolverInterface;
use MaxStan\Mercure\Api\TopicsResolverInterface;
use MaxStan\Mercure\Model\PublicTopicsResolver;
use PHPUnit\Framework\TestCase;

class PublicTopicsResolverTest extends TestCase
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGetTopicsReturnsConfiguredIris(): void
    {
        $expectedIris = [
            'https://example.com/.well-known/mercure/topic1',
            'https://example.com/.well-known/mercure/topic2',
            'https://example.com/.well-known/mercure/topic3',
        ];

        $resolver = $this->objectManager->create(
            PublicTopicsResolver::class,
            ['iris' => $expectedIris]
        );

        $actualIris = $resolver->getTopics(null);

        $this->assertSame($expectedIris, $actualIris);
    }

    public function testGetTopicsReturnsEmptyArrayByDefault(): void
    {
        $resolver = $this->objectManager->create(PublicTopicsResolver::class);

        $topics = $resolver->getTopics(null);

        $this->assertIsArray($topics);
        $this->assertEmpty($topics);
    }

    public function testGetTopicsIgnoresCustomerId(): void
    {
        $expectedIris = [
            'https://example.com/.well-known/mercure/public-topic',
        ];

        $resolver = $this->objectManager->create(
            PublicTopicsResolver::class,
            ['iris' => $expectedIris]
        );

        $topicsWithNull = $resolver->getTopics(null);
        $topicsWithZero = $resolver->getTopics(0);
        $topicsWithValidId = $resolver->getTopics(123);

        $this->assertSame($expectedIris, $topicsWithNull);
        $this->assertSame($expectedIris, $topicsWithZero);
        $this->assertSame($expectedIris, $topicsWithValidId);
        $this->assertSame($topicsWithNull, $topicsWithZero);
        $this->assertSame($topicsWithZero, $topicsWithValidId);
    }

    public function testImplementsCorrectInterfaces(): void
    {
        $resolver = $this->objectManager->create(PublicTopicsResolver::class);

        $this->assertInstanceOf(TopicsResolverInterface::class, $resolver);
        $this->assertInstanceOf(PublicTopicsResolverInterface::class, $resolver);
    }
}
