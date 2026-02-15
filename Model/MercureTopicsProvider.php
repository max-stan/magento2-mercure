<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;


use MaxStan\Mercure\Api\MercureTopicsProviderInterface;
use MaxStan\Mercure\Api\PublicTopicsResolverInterface;

readonly class MercureTopicsProvider implements MercureTopicsProviderInterface
{
    public function __construct(
        private array $publishTopicResolvers = []
    ) {
    }
    /**
     * @inheritDoc
     */
    public function getPublicTopics(?int $customerId = null): array
    {
        $topics = [];
        foreach ($this->publishTopicResolvers as $resolver) {
            if (!$resolver instanceof PublicTopicsResolverInterface) {
                continue;
            }

            $topics = [...$topics, ...$resolver->getTopics($customerId)];
        }

        return $topics;
    }

    /**
     * @inheritDoc
     */
    public function getPrivateTopics(int $customerId): array
    {
        $topics = [];
        foreach ($this->publishTopicResolvers as $resolver) {
            if ($resolver instanceof PublicTopicsResolverInterface) {
                continue;
            }

            $topics = [...$topics, ...$resolver->getTopics($customerId)];
        }

        return $topics;
    }
}
