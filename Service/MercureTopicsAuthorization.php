<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use MaxStan\Mercure\Api\MercureTopicsAuthorizationInterface;
use MaxStan\Mercure\Model\MercureTopicsProvider;

class MercureTopicsAuthorization implements MercureTopicsAuthorizationInterface
{
    public function __construct(
        private readonly MercureTopicsProvider $publishTopicProvidersPool
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllowedTopics(?int $customerId = null): array
    {
        $topics = $this->publishTopicProvidersPool->getPublicTopics();
        if ($customerId) {
            $topics = [
                ...$topics,
                ...$this->publishTopicProvidersPool->getPrivateTopics($customerId)
            ];
        }

        return $topics;
    }
}
