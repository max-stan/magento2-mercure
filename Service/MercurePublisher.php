<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MaxStan\Mercure\Api\MercurePublisherInterface;
use MaxStan\Mercure\Api\MercureTopicResolverInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Model\Jwt\TokenProvider;
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
        private readonly TokenProvider $tokenProvider,
        private readonly HubFactory $hubFactory,
        private readonly MercureTopicResolverInterface $mercureTopicResolver,
        private readonly UserContextInterface $userContext
    ) {
    }

    public function publish(array|string $topic, array $data): string
    {
        if (!$this->config->isEnabled()) {
            return '';
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

            throw $e;
        }

        $this->logger->info(
            '[MaxStan_Mercure] Mercure topic published successfully',
            ['id' => $result, 'topic' => $topic]
        );

        return $result;
    }

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
        if (!$userId) {
            return false;
        }

        if (is_string($topic)) {
            $topic = [$topic];
        }

        $privateTopics = $this->mercureTopicResolver->getAllowedPrivateTopics($userId);

        return (bool)array_intersect($topic, $privateTopics);
    }
}
