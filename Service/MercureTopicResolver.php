<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use MaxStan\Mercure\Api\MercureTopicProviderInterface;
use MaxStan\Mercure\Api\MercureTopicResolverInterface;

class MercureTopicResolver implements MercureTopicResolverInterface
{
    private array $publicTopics;
    private array $privateTopics;

    public function __construct(
        private readonly array $providers = []
    ) {
    }

    public function getAllowedPrivateTopics(int $userId, int $userType): array
    {
        if (isset($this->privateTopics[$userId])) {
            return $this->privateTopics[$userId];
        }

        /** @var MercureTopicProviderInterface[] $providers */
        $providers = $this->providers;

        $this->privateTopics[$userId] = array_reduce(
            $providers,
            fn ($acc, $provider) => [...$acc, ...$provider->getPrivateTopics($userId, $userType)],
            []
        );

        return $this->privateTopics[$userId];
    }

    public function getAllowedPublicTopics(): array
    {
        if (isset($this->publicTopics)) {
            return $this->publicTopics;
        }

        /** @var MercureTopicProviderInterface[] $providers */
        $providers = $this->providers;

        $this->publicTopics = array_reduce(
            $providers,
            fn ($acc, $provider) => [...$acc, ...$provider->getPublicTopics()],
            []
        );

        return $this->publicTopics;
    }

    public function getAllAllowedTopics(?int $userId, ?int $userType): array
    {
        $publicTopics = $this->getAllowedPublicTopics();
        if (!$userId) {
            return $publicTopics;
        }

        return [...$publicTopics, ...$this->getAllowedPrivateTopics($userId, $userType)];
    }
}
