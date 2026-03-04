<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use MaxStan\Mercure\Api\MercurePublisherInterface;
use MaxStan\Mercure\Api\MercurePublishTopicsProviderInterface;
use MaxStan\Mercure\Api\MercureSubscribeTopicsProviderInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Model\Jwt\PublisherTokenProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubFactory;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\UpdateFactory;

/**
 * Publishes updates to the Mercure Hub.
 */
class MercurePublisher implements MercurePublisherInterface
{
    private ?HubInterface $mercureHub = null;

    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger,
        private readonly Json $json,
        private readonly UpdateFactory $updateFactory,
        private readonly PublisherTokenProvider $tokenProvider,
        private readonly HubFactory $hubFactory,
        private readonly MercureSubscribeTopicsProviderInterface $mercureSubscribeTopicsProvider,
        private readonly MercurePublishTopicsProviderInterface $mercurePublishTopicsProvider,
        private readonly UserContextInterface $userContext
    ) {
    }

    /**
     * @inheritDoc
     */
    public function publish(
        array|string $topic,
        array $data,
        ?string $event = null
    ): string {
        if (!$this->config->isEnabled()) {
            return '';
        }

        if ($event) {
            $data = ['event' => $event, 'data' => $data];
        }

        /** @var Update $update */
        $update = $this->updateFactory->create([
            'topics' => $topic,
            'data' => $this->json->serialize($data),
            'private' => $this->isPrivate($topic)
        ]);

        try {
            $result = $this->getMercureHub()->publish($update);
        } catch (RuntimeException $e) {
            $this->logger->error(
                '[MaxStan_Mercure] Something went wrong during topic publish',
                ['e' => $e->getMessage(), 'topic' => $topic, 'data' => $data]
            );

            throw new LocalizedException(
                __('Something went wrong during topic publish')
            );
        }

        $this->logger->info(
            '[MaxStan_Mercure] Mercure topic published successfully',
            ['id' => $result, 'topic' => $topic]
        );

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMercureHub(): HubInterface
    {
        if ($this->mercureHub) {
            return $this->mercureHub;
        }

        $this->mercureHub = $this->hubFactory->create([
            'url' => $this->config->getHubUrl(),
            'jwtProvider' => $this->tokenProvider
        ]);

        return $this->mercureHub;
    }

    private function isPrivate(array|string $topic): bool
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();
        if (!$userId) {
            return false;
        }

        if (is_string($topic)) {
            $topic = [$topic];
        }

        $privateTopics = array_unique([
            ...$this->mercurePublishTopicsProvider->getPrivateTopics($userId, $userType),
            ...$this->mercureSubscribeTopicsProvider->getPrivateTopics($userId, $userType),
        ]);

        return (bool)array_intersect($topic, $privateTopics);
    }
}
