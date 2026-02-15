<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use MaxStan\Mercure\Api\MercureHubInterface;
use MaxStan\Mercure\Api\MercureTopicsProviderInterface;
use MaxStan\Mercure\Api\MercureTopicPublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\Update;

readonly class MercureTopicPublisher implements MercureTopicPublisherInterface
{
    public function __construct(
        private MercureHubInterface            $mercureHub,
        private UserContextInterface           $userContext,
        private MercureTopicsProviderInterface $publishTopicsProvider,
        private Json                           $json,
        private LoggerInterface                $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $topic, mixed $data): string
    {
        $customerId = $this->userContext->getUserId();
        $mercureHub = $this->mercureHub->getMercureHub($customerId);

        $update = new Update(
            $topic,
            $this->json->serialize($data),
            $this->isPrivate($customerId, $topic)
        );

        try {
            return $mercureHub->publish($update);
        } catch (RuntimeException $e) {
            $this->logger->critical(
                __('[MaxStan_Mercure]: Something went wrong during update request. %1', $e->getMessage())
            );

            throw new LocalizedException(
                __('Something went wrong during update request.'),
            );
        }
    }

    private function isPrivate(?int $customerId, string $topic): bool
    {
        return in_array($topic, $this->publishTopicsProvider->getPrivateTopics($customerId));
    }
}
