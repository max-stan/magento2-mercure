<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Service\MercurePublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubFactory;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure publisher service.
 */
#[DbIsolation(true)]
class MercurePublisherTest extends TestCase
{
    /**
     * Verify publish returns empty string and hub is never called when disabled.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_publisher_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testPublishReturnsEmptyStringWhenDisabled(): void
    {
        $mockHub = $this->createMock(HubInterface::class);
        $mockHub->expects($this->never())->method('publish');

        $publisher = $this->createPublisher($mockHub);

        $this->assertSame('', $publisher->publish('chat/1', ['msg' => 'hello']));
    }

    /**
     * Verify publish sends correct Update to hub.
     */
    #[Config('mercure/general/enabled', '1', 'store', 'default')]
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_publisher_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testPublishCallsHubWithCorrectUpdate(): void
    {
        $mockHub = $this->createMock(HubInterface::class);
        $mockHub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update): bool {
                return $update->getTopics() === ['chat/1']
                    && $update->getData() === '{"msg":"hello"}';
            }))
            ->willReturn('urn:uuid:1234');

        $publisher = $this->createPublisher($mockHub);
        $publisher->publish('chat/1', ['msg' => 'hello']);
    }

    /**
     * Verify publish returns the hub response ID.
     */
    #[Config('mercure/general/enabled', '1', 'store', 'default')]
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_publisher_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testPublishReturnsHubResponseId(): void
    {
        $mockHub = $this->createMock(HubInterface::class);
        $mockHub->method('publish')->willReturn('urn:uuid:1234');

        $publisher = $this->createPublisher($mockHub);
        $result = $publisher->publish('chat/1', ['msg' => 'hello']);

        $this->assertSame('urn:uuid:1234', $result);
    }

    /**
     * Verify publish logs success with topic and ID context.
     */
    #[Config('mercure/general/enabled', '1', 'store', 'default')]
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_publisher_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testPublishLogsSuccessAfterPublish(): void
    {
        $mockHub = $this->createMock(HubInterface::class);
        $mockHub->method('publish')->willReturn('urn:uuid:5678');

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('[MaxStan_Mercure]'),
                $this->callback(function (array $context): bool {
                    return ($context['id'] ?? null) === 'urn:uuid:5678'
                        && ($context['topic'] ?? null) === 'chat/1';
                })
            );

        $publisher = $this->createPublisher($mockHub, $mockLogger);
        $publisher->publish('chat/1', ['msg' => 'hello']);
    }

    /**
     * Verify publish logs error and throws LocalizedException on hub failure.
     */
    #[Config('mercure/general/enabled', '1', 'store', 'default')]
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_publisher_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testPublishLogsAndRethrowsOnHubError(): void
    {
        $mockHub = $this->createMock(HubInterface::class);
        $mockHub->method('publish')
            ->willThrowException(new RuntimeException('Hub unreachable'));

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('[MaxStan_Mercure]'),
                $this->callback(function (array $context): bool {
                    return ($context['e'] ?? null) === 'Hub unreachable'
                        && ($context['topic'] ?? null) === 'chat/1';
                })
            );

        $publisher = $this->createPublisher($mockHub, $mockLogger);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong during topic publish');

        $publisher->publish('chat/1', ['msg' => 'hello']);
    }

    /**
     * Create publisher with mocked hub injected via hubFactory override.
     */
    private function createPublisher(
        HubInterface $mockHub,
        ?LoggerInterface $mockLogger = null
    ): MercurePublisher {
        $objectManager = Bootstrap::getObjectManager();

        $mockHubFactory = $this->getMockBuilder(HubFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $mockHubFactory->method('create')->willReturn($mockHub);

        $args = ['hubFactory' => $mockHubFactory];
        if ($mockLogger !== null) {
            $args['logger'] = $mockLogger;
        }

        return $objectManager->create(MercurePublisher::class, $args);
    }
}
