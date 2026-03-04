<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use MaxStan\Mercure\Api\MercureTopicProviderInterface;
use MaxStan\Mercure\Api\MercureSubscribeTopicsProviderInterface;

class MercureSubscribeTopicsProvider implements MercureSubscribeTopicsProviderInterface
{
    private array $publicTopics;
    private array $privateTopics;

    public function __construct(
        private readonly array $providers = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getPrivateTopics(int $userId, int $userType): array
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

    /**
     * @inheritDoc
     */
    public function getPublicTopics(): array
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

    /**
     * @inheritDoc
     */
    public function getAllTopics(?int $userId, ?int $userType): array
    {
        $publicTopics = $this->getPublicTopics();
        if (!$userId) {
            return $publicTopics;
        }

        return [...$publicTopics, ...$this->getPrivateTopics($userId, $userType)];
    }
}
