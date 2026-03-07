<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model\Jwt;

use Magento\Authorization\Model\UserContextInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Service\MercureSubscribeTopicsProvider;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

/**
 * Generates signed JWTs for Mercure Hub authentication.
 */
class SubscriberTokenProvider implements TokenProviderInterface
{
    private ?LcobucciFactory $factory = null;

    public function __construct(
        private readonly Config $config,
        private readonly MercureSubscribeTopicsProvider $mercureTopicProviderPool,
        private readonly UserContextInterface $userContext
    ) {
    }

    public function getJwt(): string
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        return $this->getFactory()->create(
            subscribe: $this->mercureTopicProviderPool->getAllTopics($userId, $userType)
        );
    }

    private function getFactory(): LcobucciFactory
    {
        if ($this->factory) {
            return $this->factory;
        }

        $this->factory = new LcobucciFactory(
            secret: $this->config->getJwtSubscriberSecret(),
            algorithm: $this->config->getJwtSubscriberAlgorithm(),
            jwtLifetime: $this->config->getJwtSubscriberTtl()
        );

        return $this->factory;
    }
}
