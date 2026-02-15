<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\MercureHubInterface;
use MaxStan\Mercure\Api\MercureTopicsProviderInterface;
use MaxStan\Mercure\Service\MercureTopicPublisher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureTopicPublisherTest extends TestCase
{
    private const TEST_USER_ID = 42;
    private const TEST_MESSAGE_ID = 'urn:uuid:test-id';
    private const PRIVATE_TOPIC = 'https://example.com/private/chat';
    private const PUBLIC_TOPIC = 'https://example.com/public/notifications';

    private MercureTopicPublisher $publisher;
    private MercureHubInterface $mockMercureHub;
    private HubInterface $mockSymfonyHub;
    private UserContextInterface $mockUserContext;
    private MercureTopicsProviderInterface $mockTopicsProvider;
    private LoggerInterface $mockLogger;
    private Json $jsonSerializer;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Get real JSON serializer from ObjectManager
        $this->jsonSerializer = $objectManager->get(Json::class);

        // Create mock Symfony Hub
        $this->mockSymfonyHub = $this->createMock(HubInterface::class);

        // Create mock MercureHubInterface
        $this->mockMercureHub = $this->createMock(MercureHubInterface::class);
        $this->mockMercureHub->method('getMercureHub')
            ->willReturn($this->mockSymfonyHub);

        // Create mock UserContextInterface
        $this->mockUserContext = $this->createMock(UserContextInterface::class);
        $this->mockUserContext->method('getUserId')
            ->willReturn(self::TEST_USER_ID);

        // Create mock MercureTopicsProviderInterface
        $this->mockTopicsProvider = $this->createMock(MercureTopicsProviderInterface::class);
        $this->mockTopicsProvider->method('getPrivateTopics')
            ->willReturn([self::PRIVATE_TOPIC]);

        // Create mock LoggerInterface
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        // Instantiate MercureTopicPublisher with mocks
        $this->publisher = $objectManager->create(
            MercureTopicPublisher::class,
            [
                'mercureHub' => $this->mockMercureHub,
                'userContext' => $this->mockUserContext,
                'publishTopicsProvider' => $this->mockTopicsProvider,
                'json' => $this->jsonSerializer,
                'logger' => $this->mockLogger,
            ]
        );
    }

    public function testExecutePublishesSuccessfully(): void
    {
        $topic = self::PUBLIC_TOPIC;
        $data = ['message' => 'Hello World', 'timestamp' => 1234567890];

        $this->mockSymfonyHub->expects($this->once())
            ->method('publish')
            ->willReturn(self::TEST_MESSAGE_ID);

        $result = $this->publisher->execute($topic, $data);

        $this->assertEquals(self::TEST_MESSAGE_ID, $result);
    }

    public function testExecuteThrowsLocalizedExceptionOnHubFailure(): void
    {
        $topic = self::PUBLIC_TOPIC;
        $data = ['message' => 'Test'];

        $this->mockSymfonyHub->expects($this->once())
            ->method('publish')
            ->willThrowException(new RuntimeException('Hub connection failed'));

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong during update request.');

        $this->publisher->execute($topic, $data);
    }

    public function testExecuteLogsErrorOnHubFailure(): void
    {
        $topic = self::PUBLIC_TOPIC;
        $data = ['message' => 'Test'];
        $exceptionMessage = 'Hub connection failed';

        $this->mockSymfonyHub->expects($this->once())
            ->method('publish')
            ->willThrowException(new RuntimeException($exceptionMessage));

        $this->mockLogger->expects($this->once())
            ->method('critical');

        try {
            $this->publisher->execute($topic, $data);
            $this->fail('Expected LocalizedException was not thrown');
        } catch (LocalizedException $e) {
            // Expected exception
        }
    }

    public function testExecuteDetectsPrivateTopic(): void
    {
        $topic = self::PRIVATE_TOPIC;
        $data = ['message' => 'Private message'];
        $capturedUpdate = null;

        $this->mockSymfonyHub->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Update $update) use (&$capturedUpdate) {
                $capturedUpdate = $update;
                return self::TEST_MESSAGE_ID;
            });

        $this->publisher->execute($topic, $data);

        $this->assertInstanceOf(Update::class, $capturedUpdate);
        $this->assertTrue($capturedUpdate->isPrivate());
        $this->assertEquals($topic, $capturedUpdate->getTopics()[0]);
        $this->assertEquals($this->jsonSerializer->serialize($data), $capturedUpdate->getData());
    }

    public function testExecuteDetectsPublicTopic(): void
    {
        $topic = self::PUBLIC_TOPIC;
        $data = ['message' => 'Public announcement'];
        $capturedUpdate = null;

        $this->mockSymfonyHub->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Update $update) use (&$capturedUpdate) {
                $capturedUpdate = $update;
                return self::TEST_MESSAGE_ID;
            });

        $this->publisher->execute($topic, $data);

        $this->assertInstanceOf(Update::class, $capturedUpdate);
        $this->assertFalse($capturedUpdate->isPrivate());
        $this->assertEquals($topic, $capturedUpdate->getTopics()[0]);
        $this->assertEquals($this->jsonSerializer->serialize($data), $capturedUpdate->getData());
    }
}
