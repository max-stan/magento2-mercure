<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model\Jwt;

use Magento\Authorization\Model\UserContextInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Service\MercureTopicResolver;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

/**
 * Generates signed JWTs for Mercure Hub authentication.
 */
class PublisherTokenProvider implements TokenProviderInterface
{
    private ?LcobucciFactory $factory = null;

    public function __construct(
        private readonly Config $config,
        private readonly MercureTopicResolver $mercureTopicProviderPool,
        private readonly UserContextInterface $userContext
    ) {
    }

    public function getJwt(): string
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        return $this->getFactory()->create(
            publish: $this->mercureTopicProviderPool->getAllAllowedTopics($userId, $userType)
        );
    }

    private function getFactory(): LcobucciFactory
    {
        if ($this->factory) {
            return $this->factory;
        }

        $this->factory = new LcobucciFactory(
            secret: $this->config->getJwtPublisherSecret(),
            algorithm: $this->config->getJwtAlgorithm(),
            jwtLifetime: $this->config->getJwtTtl()
        );

        return $this->factory;
    }
}
